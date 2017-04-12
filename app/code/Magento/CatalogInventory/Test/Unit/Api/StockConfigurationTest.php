<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class StockConfigurationTest
 */
class StockConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogInventory\Api\StockConfigurationInterface */
    protected $stockConfiguration;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\CatalogInventory\Helper\Minsaleqty|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $minsaleqtyHelper;

    protected function setUp()
    {
        $this->config = $this->getMockForAbstractClass(
            \Magento\Catalog\Model\ProductTypes\ConfigInterface::class,
            [],
            '',
            false
        );
        $this->scopeConfig = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['isSetFlag'],
            '',
            false
        );

        $this->minsaleqtyHelper = $this->getMock(
            \Magento\CatalogInventory\Helper\Minsaleqty::class,
            [],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->stockConfiguration = $this->objectManagerHelper->getObject(
            \Magento\CatalogInventory\Model\Configuration::class,
            [
                'config' => $this->config,
                'scopeConfig' => $this->scopeConfig,
                'minsaleqtyHelper' => $this->minsaleqtyHelper
            ]
        );
    }

    public function testGetConfigItemOptions()
    {
        $configOptions = [
            'min_qty',
            'backorders',
            'min_sale_qty',
            'max_sale_qty',
            'notify_stock_qty',
            'manage_stock',
            'enable_qty_increments',
            'qty_increments',
            'is_decimal_divided',
        ];
        $this->assertSame($configOptions, $this->stockConfiguration->getConfigItemOptions());
    }

    public function testIsShowOutOfStock()
    {
        $store = 0;
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
            ->will($this->returnValue(true));
        $this->assertTrue($this->stockConfiguration->isShowOutOfStock());
    }

    public function testIsAutoReturnEnabled()
    {
        $store = 0;
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                \Magento\CatalogInventory\Model\Configuration::XML_PATH_ITEM_AUTO_RETURN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
            ->will($this->returnValue(true));
        $this->assertTrue($this->stockConfiguration->isAutoReturnEnabled());
    }

    public function testIsDisplayProductStockStatus()
    {
        $store = 0;
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                \Magento\CatalogInventory\Model\Configuration::XML_PATH_DISPLAY_PRODUCT_STOCK_STATUS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
            ->will($this->returnValue(true));
        $this->assertTrue($this->stockConfiguration->isDisplayProductStockStatus());
    }
}
