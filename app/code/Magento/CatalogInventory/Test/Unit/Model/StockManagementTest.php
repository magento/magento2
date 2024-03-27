<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock as ResourceStock;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\CatalogInventory\Model\StockState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogInventory\Model\StockManagement
 */
class StockManagementTest extends TestCase
{
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
        $this->stockResourceMock = $this->getMockBuilder(ResourceStock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockRegistryProviderMock = $this->getMockBuilder(StockRegistryProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStateMock = $this->getMockBuilder(StockState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->qtyCounterMock = $this->getMockBuilder(QtyCounterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockRegistryStorageMock = $this->getMockBuilder(StockRegistryStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemInterfaceMock = $this->getMockBuilder(StockItemInterface::class)
            ->addMethods(['hasAdminArea','getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockManagement = $this->getMockBuilder(StockManagement::class)
            ->onlyMethods(['getResource', 'canSubtractQty'])
            ->setConstructorArgs(
                [
                    'stockResource' => $this->stockResourceMock,
                    'stockRegistryProvider' => $this->stockRegistryProviderMock,
                    'stockState' => $this->stockStateMock,
                    'stockConfiguration' => $this->stockConfigurationMock,
                    'productRepository' => $this->productRepositoryMock,
                    'qtyCounter' => $this->qtyCounterMock,
                    'stockRegistryStorage' => $this->stockRegistryStorageMock,
                ]
            )->getMock();

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
            ->expects($this->any())
            ->method('hasAdminArea')
            ->willReturn(false);
    }

    /**
     * @dataProvider productsWithCorrectQtyDataProvider
     *
     * @param array $items
     * @param array $lockedItems
     * @param bool $canSubtract
     * @param bool $isQty
     * @param bool $verifyStock
     *
     * @return void
     */
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
            ->expects($this->any())
            ->method('getItemId')
            ->willReturn($lockedItems['product_id']);
        $this->stockManagement
            ->expects($this->any())
            ->method('canSubtractQty')
            ->willReturn($canSubtract);
        $this->stockConfigurationMock
            ->expects($this->any())
            ->method('isQty')
            ->willReturn($isQty);
        $this->stockItemInterfaceMock
            ->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($this->websiteId);
        $this->stockStateMock
            ->expects($this->any())
            ->method('checkQty')
            ->willReturn(true);
        $this->stockStateMock
            ->expects($this->any())
            ->method('verifyStock')
            ->willReturn($verifyStock);
        $this->stockStateMock
            ->expects($this->any())
            ->method('verifyNotification')
            ->willReturn(false);
        $this->stockResourceMock
            ->expects($this->once())
            ->method('commit');

        $this->stockManagement->registerProductsSale($items, $this->websiteId);
    }

    /**
     * @dataProvider productsWithIncorrectQtyDataProvider
     *
     * @param array $items
     * @param array $lockedItems
     *
     * @return void
     */
    public function testRegisterProductsSaleException(array $items, array $lockedItems)
    {
        $this->expectException('Magento\CatalogInventory\Model\StockStateException');
        $this->expectExceptionMessage('Some of the products are out of stock.');
        $this->stockResourceMock
            ->expects($this->once())
            ->method('beginTransaction');
        $this->stockResourceMock
            ->expects($this->once())
            ->method('lockProductsStock')
            ->willReturn([$lockedItems]);
        $this->stockItemInterfaceMock
            ->expects($this->any())
            ->method('getItemId')
            ->willReturn($lockedItems['product_id']);
        $this->stockManagement
            ->expects($this->any())
            ->method('canSubtractQty')
            ->willReturn(true);
        $this->stockConfigurationMock
            ->expects($this->any())
            ->method('isQty')
            ->willReturn(true);
        $this->stockStateMock
            ->expects($this->any())
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
