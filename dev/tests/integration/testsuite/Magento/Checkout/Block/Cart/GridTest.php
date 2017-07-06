<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Cart;

/**
 * Test to verify default config value for
 * Store->Configuration->Sales->Checkout->Shopping Cart->Number of items to display pager.
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test to verify default config value for number of items to display pager.
     *
     * @return void
     */
    public function testGetDefaultConfig()
    {
        $configValue = 20;
        /** @var $scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
        $scopeConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $defaultConfigValue = $scopeConfig->getValue(
            \Magento\Checkout\Block\Cart\Grid::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $errorMessage = 'Default Config value for Store->Configuration->Sales->Checkout->Shopping Cart->'
            . 'Number of items to display pager shouold be ' . $configValue;
        
        $this->assertEquals($configValue, $defaultConfigValue, $errorMessage);
    }
}
