<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Observer\Block\Category;

/**
 * @magentoAppArea adminhtml
 */
class AddGoogleExperimentFieldsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $config;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->config = $this->objectManager->create(
            'Magento\Config\Model\ResourceModel\Config'
        );
        $this->config->saveConfig('google/analytics/active', 1, 'default', 0);
        $this->config->saveConfig('google/analytics/type', 'universal', 'default', 0);
        $this->config->saveConfig('google/analytics/experiments', 1, 'default', 0);
        $this->config->saveConfig('google/analytics/account', 1234567890, 'default', 0);
        $this->resetConfig();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testExecute()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Catalog\Block\Adminhtml\Product\Edit\NewCategory $newCategoryForm */
        $newCategoryForm = $objectManager->get('Magento\Catalog\Block\Adminhtml\Product\Edit\NewCategory');
        $html = $newCategoryForm->toHtml();
        $this->assertContains('name="google_experiment[code_id]"', $html);
        $this->assertContains('name="google_experiment[experiment_script]"', $html);
    }

    protected function tearDown()
    {
        $this->config->deleteConfig('google/analytics/active', 'default', 0);
        $this->config->deleteConfig('google/analytics/type', 'default', 0);
        $this->config->deleteConfig('google/analytics/experiments', 'default', 0);
        $this->config->deleteConfig('google/analytics/account', 'default', 0);
        $this->resetConfig();
    }

    /**
     * Clear config cache
     */
    protected function resetConfig()
    {
        $this->objectManager->get('Magento\Framework\App\Config\ReinitableConfigInterface')->reinit();
    }
}
