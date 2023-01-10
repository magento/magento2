<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\TestFramework\Application;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for PluginListGeneratorTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PluginListGeneratorTest extends TestCase
{
    /**
     * Generated plugin list config for frontend scope
     */
    const CACHE_ID_FRONTEND = 'primary|global|frontend|plugin-list';

    /**
     * Generated plugin list config for dummy scope
     */
    const CACHE_ID_DUMMY = 'primary|global|dummy|plugin-list';

    private $cacheIds = [self::CACHE_ID_FRONTEND, self::CACHE_ID_DUMMY];

    /**
     * @var PluginListGenerator
     */
    private $model;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var DriverInterface
     */
    private $file;

    /**
     * @var Application
     */
    private $application;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->application = Bootstrap::getInstance()->getBootstrap()->getApplication();
        $this->directoryList = new DirectoryList(BP, $this->getCustomDirs());
        $this->file = Bootstrap::getObjectManager()->create(DriverInterface::class);
        $reader = Bootstrap::getObjectManager()->create(
        // phpstan:ignore "Class Magento\Framework\ObjectManager\Config\Reader\Dom\Proxy not found."
            \Magento\Framework\ObjectManager\Config\Reader\Dom\Proxy::class
        );
        $scopeConfig = Bootstrap::getObjectManager()->create(\Magento\Framework\Config\Scope::class);
        $omConfig = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Interception\ObjectManager\Config\Developer::class
        );
        $relations = Bootstrap::getObjectManager()->create(
            \Magento\Framework\ObjectManager\Relations\Runtime::class
        );
        $definitions = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Interception\Definition\Runtime::class
        );
        $classDefinitions = Bootstrap::getObjectManager()->create(
            \Magento\Framework\ObjectManager\Definition\Runtime::class
        );
        // phpstan:ignore "Class Psr\Log\LoggerInterface\Proxy not found."
        $logger = Bootstrap::getObjectManager()->create(\Psr\Log\LoggerInterface\Proxy::class);
        $this->model = new PluginListGenerator(
            $reader,
            $scopeConfig,
            $omConfig,
            $relations,
            $definitions,
            $classDefinitions,
            $logger,
            $this->directoryList,
            ['primary', 'global']
        );
    }

    /**
     * Test plugin list configuration generation and load.
     */
    public function testPluginListConfigGeneration()
    {
        $scopes = ['global', 'frontend', 'dummy'];
        $globalPlugin = 'genericHeaderPlugin';
        $frontendPlugin = 'response-http-page-cache';
        $this->model->write($scopes);
        $configDataFrontend = $this->model->load(self::CACHE_ID_FRONTEND);
        $this->assertNotEmpty($configDataFrontend[0]);
        $this->assertNotEmpty($configDataFrontend[1]);
        $this->assertNotEmpty($configDataFrontend[2]);
        $expectedFrontend = [
            1 => [
                0 => $globalPlugin,
                1 => $frontendPlugin
            ]
        ];
        // Here in test is assumed that this class below has 3 plugins. But the amount of plugins and class itself
        // may vary. If it is changed, please update these assertions.
        $this->assertArrayHasKey(
            'Magento\\Framework\\App\\Response\\Http_sendResponse___self',
            $configDataFrontend[2],
            'Processed plugin does not exist in the processed plugins array.'
        );

        $this->assertSame(
            $expectedFrontend,
            $configDataFrontend[2]['Magento\\Framework\\App\\Response\\Http_sendResponse___self'],
            'Plugin configurations are not equal'
        );

        $configDataDummy = $this->model->load(self::CACHE_ID_DUMMY);
        /**
         * Make sure "dummy" scope with no plugins in system should not contain plugins from "frontend" scope
         */
        $this->assertNotContains(
            $frontendPlugin,
            $configDataDummy[2]['Magento\\Framework\\App\\Response\\Http_sendResponse___self'][1],
            'Plugin configurations are not equal. "dummy" scope should not contain plugins from "frontend" scope'
        );
        /**
         * Make sure "dummy" scope with no plugins in system should contain plugins from "global" scope
         */
        $this->assertContains(
            $globalPlugin,
            $configDataDummy[2]['Magento\\Framework\\App\\Response\\Http_sendResponse___self'][1],
            'Plugin configurations are not equal. "dummy" scope should contain plugins from "global" scope'
        );
    }

    /**
     * Gets customized directory paths
     *
     * @return array
     */
    private function getCustomDirs(): array
    {
        $path = DirectoryList::PATH;
        $generated = "{$this->application->getTempDir()}/generated";

        return [
            DirectoryList::GENERATED_METADATA => [$path => "{$generated}/metadata"],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        foreach ($this->cacheIds as $cacheId) {
            $filePath = $this->directoryList->getPath(DirectoryList::GENERATED_METADATA)
                . '/' . $cacheId . '.' . 'php';

            if (file_exists($filePath)) {
                $this->file->deleteFile($filePath);
            }
        }
    }
}
