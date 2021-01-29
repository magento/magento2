<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Mode;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test config manager.
 */
class ConfigManagerTest extends TestCase
{
    /**
     * @var ConfigManager
     */
    private $manager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->manager = Bootstrap::getObjectManager()->get(ConfigManager::class);
    }

    /**
     * Check the default configurations of CSP.
     *
     * @magentoAppArea frontend
     * @return void
     */
    public function testStorefrontDefault(): void
    {
        $config = $this->manager->getConfigured();
        $this->assertTrue($config->isReportOnly());
        $this->assertNull($config->getReportUri());
    }

    /**
     * Check the default configurations of CSP.
     *
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testAdminDefault(): void
    {
        $config = $this->manager->getConfigured();
        $this->assertTrue($config->isReportOnly());
        $this->assertNull($config->getReportUri());
    }

    /**
     * Check that class returns correct configurations.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 0
     * @magentoConfigFixture default_store csp/mode/storefront/report_uri https://magento.com
     * @return void
     */
    public function testFrontendConfigured(): void
    {
        $config = $this->manager->getConfigured();
        $this->assertFalse($config->isReportOnly());
        $this->assertEquals('https://magento.com', $config->getReportUri());
    }

    /**
     * Check that class returns correct configurations.
     *
     * @magentoAppArea adminhtml
     * @magentoConfigFixture default_store csp/mode/admin/report_only 0
     * @magentoConfigFixture default_store csp/mode/admin/report_uri https://magento.com
     * @return void
     */
    public function testAdminConfigured(): void
    {
        $config = $this->manager->getConfigured();
        $this->assertFalse($config->isReportOnly());
        $this->assertEquals('https://magento.com', $config->getReportUri());
    }
}
