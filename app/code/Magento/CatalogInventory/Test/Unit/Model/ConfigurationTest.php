<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model;

use \Magento\CatalogInventory\Model\Configuration;

/**
 * Class ConfigurationTest
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $minSaleQtyHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->minSaleQtyHelperMock = $this->getMockBuilder(\Magento\CatalogInventory\Helper\Minsaleqty::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Configuration(
            $this->configMock,
            $this->scopeConfigMock,
            $this->minSaleQtyHelperMock,
            $this->storeManagerMock
        );
    }

    public function testGetDefaultWebsiteId()
    {
        $this->assertEquals(0, $this->model->getDefaultScopeId());
    }

    public function testGetIsQtyTypeIds()
    {
        $filter = 3;
        $configData = [1 => ['is_qty' => 1], 2 => ['is_qty' => 2], 3 => ['is_qty' => 3]];

        $this->configMock->expects($this->any())
            ->method('getAll')
            ->willReturn($configData);
        $this->assertEquals([3 => '3'], $this->model->getIsQtyTypeIds($filter));
    }

    public function testIsQty()
    {
        $configData = [1 => ['is_qty' => 1], 2 => ['is_qty' => 2], 3 => ['is_qty' => 3]];
        $productTypeId = 1;

        $this->configMock->expects($this->any())
            ->method('getAll')
            ->willReturn($configData);
        $this->assertEquals($productTypeId, $this->model->isQty($productTypeId));
    }

    public function testCanSubtractQty()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Configuration::XML_PATH_CAN_SUBTRACT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(true);
        $this->assertTrue($this->model->canSubtractQty(1));
    }

    public function testGetMinQty()
    {
        $qty = 1;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Configuration::XML_PATH_MIN_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 1)
            ->willReturn($qty);
        $this->assertEquals($qty, $this->model->getMinQty(1));
    }

    public function testGetMinSaleQty()
    {
        $store = 1;
        $customerGroupId = 2;

        $this->minSaleQtyHelperMock->expects($this->once())
            ->method('getConfigValue')
            ->with($customerGroupId, $store)
            ->willReturn(1);

        $this->assertEquals(1.0, $this->model->getMinSaleQty($store, $customerGroupId));
    }

    public function testGetMaxSaleQty()
    {
        $store = 1;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Configuration::XML_PATH_MAX_SALE_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->willReturn(1);
        $this->assertEquals(1, $this->model->getMaxSaleQty($store));
    }

    public function testGetNotifyStockQty()
    {
        $store = 1;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Configuration::XML_PATH_NOTIFY_STOCK_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->willReturn(1);
        $this->assertEquals(1, $this->model->getNotifyStockQty($store));
    }

    public function testGetEnableQtyIncrements()
    {
        $store = 1;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Configuration::XML_PATH_ENABLE_QTY_INCREMENTS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )->willReturn(1);
        $this->assertEquals(1, $this->model->getEnableQtyIncrements($store));
    }

    public function testGetQtyIncrements()
    {
        $store = 1;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Configuration::XML_PATH_QTY_INCREMENTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->willReturn(1);
        $this->assertEquals(1, $this->model->getQtyIncrements($store));
    }

    public function testGetBackorders()
    {
        $store = 1;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Configuration::XML_PATH_BACKORDERS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->willReturn(1);
        $this->model->getBackorders($store);
    }

    public function testGetCanBackInStock()
    {
        $store = 1;
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Configuration::XML_PATH_CAN_BACK_IN_STOCK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->willReturn(1);
        $this->assertEquals(1, $this->model->getCanBackInStock($store));
    }

    public function testIsShowOutOfStock()
    {
        $store = 1;
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->willReturn(1);
        $this->assertEquals(1, $this->model->isShowOutOfStock($store));
    }

    public function testIsAutoReturnEnabled()
    {
        $store = 1;
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Configuration::XML_PATH_ITEM_AUTO_RETURN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->willReturn(1);
        $this->assertEquals(1, $this->model->isAutoReturnEnabled($store));
    }

    public function testIsDisplayProductStockStatus()
    {
        $store = 1;
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Configuration::XML_PATH_DISPLAY_PRODUCT_STOCK_STATUS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->model->isDisplayProductStockStatus($store));
    }

    public function testGetDefaultConfigValue()
    {
        $field = 'test_field';
        $store = 1;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Configuration::XML_PATH_ITEM . $field,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
            ->willReturn(1);
        $this->assertEquals(1, $this->model->getDefaultConfigValue($field, $store));
    }

    public function testGetConfigItemOptions()
    {
        $fields = [
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
        $this->assertEquals($fields, $this->model->getConfigItemOptions());
    }

    public function testGetManageStock()
    {
        $store = 1;
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Configuration::XML_PATH_MANAGE_STOCK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->willReturn(1);
        $this->assertEquals(1, $this->model->getManageStock($store));
    }
}
