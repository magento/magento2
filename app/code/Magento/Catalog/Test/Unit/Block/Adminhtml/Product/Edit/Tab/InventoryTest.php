<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

/**
 * Class InventoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InventoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\CatalogInventory\Model\Source\Stock|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockMock;

    /**
     * @var \Magento\CatalogInventory\Model\Source\Backorders|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $backordersMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockConfigurationMock;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
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
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->createPartialMock(
            \Magento\Backend\Block\Template\Context::class,
            ['getRequest', 'getStoreManager']
        );
        $this->stockConfigurationMock = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockConfigurationInterface::class,
            [],
            '',
            false
        );
        $this->stockRegistryMock =  $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class,
            [],
            '',
            false
        );
        $this->backordersMock = $this->createMock(\Magento\CatalogInventory\Model\Source\Backorders::class);
        $this->stockMock = $this->createMock(\Magento\CatalogInventory\Model\Source\Stock::class);
        $this->coreRegistryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->moduleManager = $this->createMock(\Magento\Framework\Module\Manager::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->contextMock->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $this->inventory = $objectManager->getObject(
            \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Inventory::class,
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
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId', 'getStore']);
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
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

        $stockItemMock = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class,
            [],
            '',
            false,
            false,
            false,
            $methods
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
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

        $stockItemMock = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class,
            [],
            '',
            false,
            false,
            false,
            $methods
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
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
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getInventoryReadonly']);
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
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId']);
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
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeInstance']);
        $typeMock = $this->getMockForAbstractClass(
            \Magento\Catalog\Model\Product\Type\AbstractType::class,
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
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getIsVirtual']);
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
