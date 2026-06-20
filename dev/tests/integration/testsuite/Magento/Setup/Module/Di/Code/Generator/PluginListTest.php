<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Module\Di\Code\Generator;

use Magento\Framework\App\Area;
use Magento\Framework\App\Interception\Cache\CompiledConfig;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Setup PluginList reset() behavior across area scopes.
 */
class PluginListTest extends TestCase
{
    /**
     * @var PluginList
     */
    private PluginList $pluginList;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->pluginList = $objectManager->create(
            PluginList::class,
            [
                'cache' => $objectManager->get(CompiledConfig::class),
                'cacheId' => 'test-setup-pluginlist-' . uniqid('', true),
                'scopePriorityScheme' => [Area::AREA_GLOBAL],
            ]
        );
        $this->pluginList->setInterceptedClasses([HttpResponse::class]);
    }

    /**
     * Magento\Framework\App\Response\Http has a global plugin in Store/etc/di.xml
     * and an additional frontend plugin in PageCache/etc/frontend/di.xml.
     *
     * This verifies reset() clears loaded scopes so PluginList can be reused
     * across area iterations and rebuild frontend plugin data after a global load.
     */
    public function testResetAllowsPluginListToBeRebuiltForNextArea(): void
    {
        $globalConfig = $this->pluginList->getPluginsConfig();
        $globalPlugins = array_keys($globalConfig[HttpResponse::class] ?? []);

        $this->assertContains('genericHeaderPlugin', $globalPlugins);
        $this->assertNotContains('response-http-page-cache', $globalPlugins);

        $this->pluginList->reset();
        $this->pluginList->setScopePriorityScheme([Area::AREA_GLOBAL, Area::AREA_FRONTEND]);

        $frontendConfig = $this->pluginList->getPluginsConfig();
        $frontendPlugins = array_keys($frontendConfig[HttpResponse::class] ?? []);

        $this->assertContains('genericHeaderPlugin', $frontendPlugins);
        $this->assertContains('response-http-page-cache', $frontendPlugins);
    }
}
