<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Inventory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Source\Backorders;
use Magento\CatalogInventory\Model\Source\Stock;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InventoryTest extends TestCase
{
    /**
     * @var Manager|MockObject
     */
    protected $moduleManager;

    /**
     * @var Registry|MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var Stock|MockObject
     */
    protected $stockMock;

    /**
     * @var Backorders|MockObject
     */
    protected $backordersMock;

    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    protected $stockConfigurationMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Inventory
     */
    protected $inventory;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getStoreManager']
        );
        $this->stockConfigurationMock = $this->getMockForAbstractClass(
            StockConfigurationInterface::class,
            [],
            '',
            false
        );
        $this->stockRegistryMock =  $this->getMockForAbstractClass(
            StockRegistryInterface::class,
            [],
            '',
            false
        );
        $this->backordersMock = $this->createMock(Backorders::class);
        $this->stockMock = $this->createMock(Stock::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->moduleManager = $this->createMock(Manager::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->contextMock->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $this->inventory = $objectManager->getObject(
            Inventory::class,
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
            ->willReturn($moduleEnabled);
        if ($moduleEnabled) {
            $this->backordersMock->expects($this->once())
                ->method('toOptionArray')
                ->willReturn(['test-value', 'test-value']);
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
            ->willReturn($moduleEnabled);
        if ($moduleEnabled) {
            $this->stockMock->expects($this->once())
                ->method('toOptionArray')
                ->willReturn(['test-value', 'test-value']);
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
            ->willReturn('return-value');

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
        $productMock = $this->createPartialMock(Product::class, ['getId', 'getStore']);
        $storeMock = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->willReturn('return-value');

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

        $stockItemMock = $this->getMockBuilder(StockItemInterface::class)
                            ->disableOriginalConstructor()
                            ->addMethods($methods)
                            ->getMockForAbstractClass();
        $productMock = $this->createMock(Product::class);
        $storeMock = $this->createMock(Store::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->willReturn($stockItemMock);
        $stockItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($stockId);

        if (!empty($methods)) {
            $stockItemMock->expects($this->once())
                ->method(reset($methods))
                ->willReturn('call-method');
        }
        if (empty($methods) || empty($stockId)) {
            $this->stockConfigurationMock->expects($this->once())
                ->method('getDefaultConfigValue')
                ->willReturn('default-result');
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

        $stockItemMock = $this->getMockBuilder(StockItemInterface::class)
            ->disableOriginalConstructor()
            ->addMethods($methods)
            ->getMockForAbstractClass();
        $productMock = $this->createMock(Product::class);
        $storeMock = $this->createMock(Store::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->willReturn($stockItemMock);
        $stockItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($stockId);

        if (!empty($methods)) {
            $stockItemMock->expects($this->once())
                ->method(reset($methods))
                ->willReturn('call-method');
        }
        if (empty($methods) || empty($stockId)) {
            $this->stockConfigurationMock->expects($this->once())
                ->method('getDefaultConfigValue')
                ->willReturn('default-result');
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
            ->willReturn('return-value');

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
        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getInventoryReadonly'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('getInventoryReadonly')
            ->willReturn('return-value');

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
        $productMock = $this->createPartialMock(Product::class, ['getId']);
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);

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
        $productMock = $this->createPartialMock(Product::class, ['getTypeInstance']);
        $typeMock = $this->getMockForAbstractClass(
            AbstractType::class,
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
            ->willReturn($productMock);
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeMock);
        $typeMock->expects($this->once())
            ->method('canUseQtyDecimals')
            ->willReturn('return-value');

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
        $productMock = $this->createPartialMock(Product::class, ['getIsVirtual']);
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);
        $productMock->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn('return-value');

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
            ->willReturn('return-value');

        $result = $this->inventory->isSingleStoreMode();
        $this->assertEquals('return-value', $result);
    }

    /**
     * Data for Module Enabled
     *
     * @return array
     */
    public static function dataProviderModuleEnabled()
    {
        return [
            [
                'moduleEnabled' => true,
            ],
            [
                'moduleEnabled' => false
            ]
        ];
    }

    /**
     * Data for getFieldValue method
     *
     * @return array
     */
    public static function dataProviderGetFieldValue()
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
    public static function dataProviderGetConfigFieldValue()
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
    public static function dataProviderGetId()
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
