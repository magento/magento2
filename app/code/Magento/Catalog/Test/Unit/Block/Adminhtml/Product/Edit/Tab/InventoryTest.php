<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

/**
 * Class InventoryTest
 */
class InventoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\CatalogInventory\Model\Source\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockMock;

    /**
     * @var \Magento\CatalogInventory\Model\Source\Backorders|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backordersMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfigurationMock;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Inventory
     */
    protected $inventory;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->getMock(
            'Magento\Backend\Block\Template\Context',
            ['getRequest', 'getStoreManager'],
            [],
            '',
            false
        );
        $this->stockConfigurationMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockConfigurationInterface',
            [],
            '',
            false
        );
        $this->stockRegistryMock =  $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockRegistryInterface',
            [],
            '',
            false
        );
        $this->backordersMock = $this->getMock(
            'Magento\CatalogInventory\Model\Source\Backorders',
            [],
            [],
            '',
            false
        );
        $this->stockMock = $this->getMock(
            'Magento\CatalogInventory\Model\Source\Stock',
            [],
            [],
            '',
            false
        );
        $this->coreRegistryMock = $this->getMock(
            'Magento\Framework\Registry',
            [],
            [],
            '',
            false
        );
        $this->moduleManager = $this->getMock(
            'Magento\Framework\Module\Manager',
            [],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false
        );

        $this->contextMock->expects($this->once())
            ->method('getStoreManager')
            ->will($this->returnValue($this->storeManagerMock));

        $this->inventory = $objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Inventory',
            [
                'context' => $this->contextMock,
                'backorders' => $this->backordersMock,
                'stock' => $this->stockMock,
                'moduleManager' => $this->moduleManager,
                'coreRegistry' => $this->coreRegistryMock,
                'stockRegistry' => $this->stockRegistryMock,
                'stockConfiguration' => $this->stockConfigurationMock,
            ]
        );
    }

    /**
     * Run test getBackordersOption method
     *
     * @param bool $moduleEnabled
     * @return void
     *
     * @dataProvider dataProviderModuleEnabled
     */
    public function testGetBackordersOption($moduleEnabled)
    {
        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_CatalogInventory')
            ->will($this->returnValue($moduleEnabled));
        if ($moduleEnabled) {
            $this->backordersMock->expects($this->once())
                ->method('toOptionArray')
                ->will($this->returnValue(['test-value', 'test-value']));
        }

        $result = $this->inventory->getBackordersOption();
        $this->assertEquals($moduleEnabled, !empty($result));
    }

    /**
     * Run test getStockOption method
     *
     * @param bool $moduleEnabled
     * @return void
     *
     * @dataProvider dataProviderModuleEnabled
     */
    public function testGetStockOption($moduleEnabled)
    {
        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_CatalogInventory')
            ->will($this->returnValue($moduleEnabled));
        if ($moduleEnabled) {
            $this->stockMock->expects($this->once())
                ->method('toOptionArray')
                ->will($this->returnValue(['test-value', 'test-value']));
        }

        $result = $this->inventory->getStockOption();
        $this->assertEquals($moduleEnabled, !empty($result));
    }

    /**
     * Run test getProduct method
     *
     * @return void
     */
    public function testGetProduct()
    {
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue('return-value'));

        $result = $this->inventory->getProduct();
        $this->assertEquals('return-value', $result);
    }

    /**
     * Run test getStockItem method
     *
     * @return void
     */
    public function testGetStockItem()
    {
        $productId = 10;
        $websiteId = 15;
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'getStore'],
            [],
            '',
            false
        );
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['getWebsiteId'],
            [],
            '',
            false
        );
        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $productMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue($productMock));
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->will($this->returnValue('return-value'));

        $resultItem = $this->inventory->getStockItem();
        $this->assertEquals('return-value', $resultItem);
    }

    /**
     * Run test getFieldValue method
     *
     * @param int $stockId
     * @param array $methods
     * @param string $result
     * @return void
     *
     * @dataProvider dataProviderGetFieldValue
     */
    public function testGetFieldValue($stockId, $methods, $result)
    {
        $productId = 10;
        $websiteId = 15;
        $fieldName = 'field';

        $stockItemMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockItemInterface',
            [],
            '',
            false,
            false,
            false,
            $methods
        );
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $productMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue($productMock));
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->will($this->returnValue($stockItemMock));
        $stockItemMock->expects($this->once())
            ->method('getItemId')
            ->will($this->returnValue($stockId));

        if (!empty($methods)) {
            $stockItemMock->expects($this->once())
                ->method(reset($methods))
                ->will($this->returnValue('call-method'));
        }
        if (empty($methods) || empty($stockId)) {
            $this->stockConfigurationMock->expects($this->once())
                ->method('getDefaultConfigValue')
                ->will($this->returnValue('default-result'));
        }

        $resultValue = $this->inventory->getFieldValue($fieldName);
        $this->assertEquals($result, $resultValue);
    }

    /**
     * Run test getConfigFieldValue method
     *
     * @param int $stockId
     * @param array $methods
     * @param string $result
     * @return void
     *
     * @dataProvider dataProviderGetConfigFieldValue
     */
    public function testGetConfigFieldValue($stockId, $methods, $result)
    {
        $productId = 10;
        $websiteId = 15;
        $fieldName = 'field';

        $stockItemMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockItemInterface',
            [],
            '',
            false,
            false,
            false,
            $methods
        );
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $productMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue($productMock));
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->will($this->returnValue($stockItemMock));
        $stockItemMock->expects($this->once())
            ->method('getItemId')
            ->will($this->returnValue($stockId));

        if (!empty($methods)) {
            $stockItemMock->expects($this->once())
                ->method(reset($methods))
                ->will($this->returnValue('call-method'));
        }
        if (empty($methods) || empty($stockId)) {
            $this->stockConfigurationMock->expects($this->once())
                ->method('getDefaultConfigValue')
                ->will($this->returnValue('default-result'));
        }

        $resultField = $this->inventory->getConfigFieldValue($fieldName);
        $this->assertEquals($result, $resultField);
    }

    /**
     * Run test getDefaultConfigValue method
     *
     * @return void
     */
    public function testGetDefaultConfigValue()
    {
        $field = 'filed-name';
        $this->stockConfigurationMock->expects($this->once())
            ->method('getDefaultConfigValue')
            ->will($this->returnValue('return-value'));

        $result = $this->inventory->getDefaultConfigValue($field);
        $this->assertEquals('return-value', $result);
    }

    /**
     * Run test isReadonly method
     *
     * @return void
     */
    public function testIsReadonly()
    {
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getInventoryReadonly'],
            [],
            '',
            false
        );
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue($productMock));

        $productMock->expects($this->once())
            ->method('getInventoryReadonly')
            ->will($this->returnValue('return-value'));

        $result = $this->inventory->isReadonly();
        $this->assertEquals('return-value', $result);
    }

    /**
     * Run test isNew method
     *
     * @param int|null $id
     * @param bool $result
     * @return void
     *
     * @dataProvider dataProviderGetId
     */
    public function testIsNew($id, $result)
    {
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId'],
            [],
            '',
            false
        );
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue($productMock));
        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));

        $methodResult = $this->inventory->isNew();
        $this->assertEquals($result, $methodResult);
    }

    /**
     * Run test getFieldSuffix method
     *
     * @return void
     */
    public function testGetFieldSuffix()
    {
        $result = $this->inventory->getFieldSuffix();
        $this->assertEquals('product', $result);
    }

    /**
     * Run test canUseQtyDecimals method
     *
     * @return void
     */
    public function testCanUseQtyDecimals()
    {
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getTypeInstance'],
            [],
            '',
            false
        );
        $typeMock = $this->getMockForAbstractClass(
            'Magento\Catalog\Model\Product\Type\AbstractType',
            [],
            '',
            false,
            true,
            true,
            ['canUseQtyDecimals']
        );
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue($productMock));
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeMock));
        $typeMock->expects($this->once())
            ->method('canUseQtyDecimals')
            ->will($this->returnValue('return-value'));

        $result = $this->inventory->canUseQtyDecimals();
        $this->assertEquals('return-value', $result);
    }

    /**
     * Run test isVirtual method
     *
     * @return void
     */
    public function testIsVirtual()
    {
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getIsVirtual'],
            [],
            '',
            false
        );
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue($productMock));
        $productMock->expects($this->once())
            ->method('getIsVirtual')
            ->will($this->returnValue('return-value'));

        $result = $this->inventory->isVirtual();
        $this->assertEquals('return-value', $result);
    }

    /**
     * Run test isSingleStoreMode method
     *
     * @return void
     */
    public function testIsSingleStoreMode()
    {
        $this->storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->will($this->returnValue('return-value'));

        $result = $this->inventory->isSingleStoreMode();
        $this->assertEquals('return-value', $result);
    }

    /**
     * Data for Module Enabled
     *
     * @return array
     */
    public function dataProviderModuleEnabled()
    {
        return [
            [
                'ModuleEnabled' => true,
            ],
            [
                'ModuleEnabled' => false
            ]
        ];
    }

    /**
     * Data for getFieldValue method
     *
     * @return array
     */
    public function dataProviderGetFieldValue()
    {
        return [
            [
                'stockId' => 99,
                'methods' => ['getField'],
                'result' => 'call-method',
            ],
            [
                'stockId' => null,
                'methods' => [],
                'result' => 'default-result'
            ],
            [
                'stockId' => 99,
                'methods' => [],
                'result' => 'default-result'
            ]
        ];
    }

    /**
     * Data for getConfigFieldValue and getFieldValue method
     *
     * @return array
     */
    public function dataProviderGetConfigFieldValue()
    {
        return [
            [
                'stockId' => 99,
                'methods' => ['getUseConfigField'],
                'result' => 'call-method',
            ],
            [
                'stockId' => null,
                'methods' => [],
                'result' => 'default-result'
            ],
            [
                'stockId' => 99,
                'methods' => [],
                'result' => 'default-result'
            ]
        ];
    }

    /**
     * Data for isNew method
     *
     * @return array
     */
    public function dataProviderGetId()
    {
        return [
            [
                'id' => 99,
                'result' => false,
            ],
            [
                'id' => null,
                'result' => true
            ]
        ];
    }
}
