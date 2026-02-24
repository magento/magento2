<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock as ResourceStock;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\CatalogInventory\Model\StockState;
use Magento\CatalogInventory\Model\StockStateException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogInventory\Model\StockManagement
 */
class StockManagementTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var StockManagement|MockObject
     */
    private $stockManagement;

    /**
     * @var ResourceStock|MockObject
     */
    private $stockResourceMock;

    /**
     * @var StockRegistryProviderInterface|MockObject
     */
    private $stockRegistryProviderMock;

    /**
     * @var StockState|MockObject
     */
    private $stockStateMock;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var QtyCounterInterface|MockObject
     */
    private $qtyCounterMock;

    /**
     * @var StockRegistryStorage|MockObject
     */
    private $stockRegistryStorageMock;

    /**
     * @var StockItemInterface|MockObject
     */
    private $stockItemInterfaceMock;

    /**
     * @var int
     */
    private $websiteId = 0;

    protected function setUp(): void
    {
        $this->stockResourceMock = $this->createMock(ResourceStock::class);
        $this->stockRegistryProviderMock = $this->createMock(StockRegistryProviderInterface::class);
        $this->stockStateMock = $this->createMock(StockState::class);
        $this->stockConfigurationMock = $this->createMock(StockConfigurationInterface::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->qtyCounterMock = $this->createMock(QtyCounterInterface::class);
        $this->stockRegistryStorageMock = $this->createMock(StockRegistryStorage::class);
        $this->stockItemInterfaceMock = $this->createPartialMockWithReflection(
            Item::class,
            ['hasAdminArea', 'getWebsiteId', 'getItemId']
        );
        
        // Use getMockBuilder for partial mock with constructor args
        $mockBuilder = $this->getMockBuilder(StockManagement::class);
        $mockBuilder->onlyMethods(['getResource', 'canSubtractQty']);
        $mockBuilder->setConstructorArgs([
            $this->stockResourceMock,
            $this->stockRegistryProviderMock,
            $this->stockStateMock,
            $this->stockConfigurationMock,
            $this->productRepositoryMock,
            $this->qtyCounterMock,
            $this->stockRegistryStorageMock,
        ]);
        $this->stockManagement = $mockBuilder->getMock();

        $this->stockConfigurationMock
            ->expects($this->once())
            ->method('getDefaultScopeId')
            ->willReturn($this->websiteId);
        $this->stockManagement
            ->expects($this->any())
            ->method('getResource')
            ->willReturn($this->stockResourceMock);
        $this->stockRegistryProviderMock
            ->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemInterfaceMock);
        $this->stockItemInterfaceMock
            ->method('hasAdminArea')
            ->willReturn(false);
    }

    /**
     * @param array $items
     * @param array $lockedItems
     * @param bool $canSubtract
     * @param bool $isQty
     * @param bool $verifyStock
     *
     * @return void
     */
    #[DataProvider('productsWithCorrectQtyDataProvider')]
    public function testRegisterProductsSale(
        array $items,
        array $lockedItems,
        bool $canSubtract,
        bool $isQty,
        bool $verifyStock = true
    ) {
        $this->stockResourceMock
            ->expects($this->once())
            ->method('beginTransaction');
        $this->stockResourceMock
            ->expects($this->once())
            ->method('lockProductsStock')
            ->willReturn([$lockedItems]);
        $this->stockItemInterfaceMock
            ->method('getItemId')
            ->willReturn($lockedItems['product_id']);
        $this->stockManagement
            ->expects($this->any())
            ->method('canSubtractQty')
            ->willReturn($canSubtract);
        $this->stockConfigurationMock
            ->method('isQty')
            ->willReturn($isQty);
        $this->stockItemInterfaceMock
            ->method('getWebsiteId')
            ->willReturn($this->websiteId);
        $this->stockStateMock
            ->method('checkQty')
            ->willReturn(true);
        $this->stockStateMock
            ->method('verifyStock')
            ->willReturn($verifyStock);
        $this->stockStateMock
            ->method('verifyNotification')
            ->willReturn(false);
        $this->stockResourceMock
            ->expects($this->once())
            ->method('commit');

        $this->stockManagement->registerProductsSale($items, $this->websiteId);
    }

    /**
     * @param array $items
     * @param array $lockedItems
     *
     * @return void
     */
    #[DataProvider('productsWithIncorrectQtyDataProvider')]
    public function testRegisterProductsSaleException(array $items, array $lockedItems)
    {
        $this->expectException(StockStateException::class);
        $this->expectExceptionMessage('Some of the products are out of stock.');
        $this->stockResourceMock
            ->expects($this->once())
            ->method('beginTransaction');
        $this->stockResourceMock
            ->expects($this->once())
            ->method('lockProductsStock')
            ->willReturn([$lockedItems]);
        $this->stockItemInterfaceMock
            ->method('getItemId')
            ->willReturn($lockedItems['product_id']);
        $this->stockManagement
            ->expects($this->any())
            ->method('canSubtractQty')
            ->willReturn(true);
        $this->stockConfigurationMock
            ->method('isQty')
            ->willReturn(true);
        $this->stockStateMock
            ->method('checkQty')
            ->willReturn(false);
        $this->stockResourceMock
            ->expects($this->once())
            ->method('commit');

        $this->stockManagement->registerProductsSale($items, $this->websiteId);
    }

    /**
     * @return array
     */
    public static function productsWithCorrectQtyDataProvider(): array
    {
        return [
            [
                [1 => 3],
                [
                    'product_id' => 1,
                    'qty' => 10,
                    'type_id' => 'simple',
                ],
                false,
                false,
            ],
            [
                [2 => 4],
                [
                    'product_id' => 2,
                    'qty' => 10,
                    'type_id' => 'simple',
                ],
                true,
                true,
            ],
            [
                [3 => 5],
                [
                    'product_id' => 3,
                    'qty' => 10,
                    'type_id' => 'simple',
                ],
                true,
                true,
                false,
            ],
        ];
    }

    /**
     * @return array
     */
    public static function productsWithIncorrectQtyDataProvider(): array
    {
        return [
            [
                [2 => 4],
                [
                    'product_id' => 2,
                    'qty' => 2,
                    'type_id' => 'simple',
                ],
            ],
        ];
    }
}
