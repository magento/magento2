<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\CatalogInventory\Model\StockStateProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock;

/**
 * StockRegistry test.
 */
class StockStateProviderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StockStateProvider
     */
    private $model;

    /**
     * @var StockItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $stockItem;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->stockItem = $this->getMockBuilder(StockItemInterface::class)
            ->getMock();

        $this->model = $this->objectManager->getObject(StockStateProvider::class);
    }

    /**
     * Tests verifyStock method.
     *
     * @param int $qty
     * @param int $backOrders
     * @param int $minQty
     * @param int $manageStock
     * @param int $expected
     *
     * @return void
     *
     * @dataProvider stockItemDataProvider
     * @covers \Magento\CatalogInventory\Model\StockStateProvider::verifyStock
     */
    public function testVerifyStock(
        ?int $qty,
        ?int $backOrders,
        ?int $minQty,
        ?int $manageStock,
        bool $expected
    ): void {
        $this->stockItem->method('getQty')
            ->willReturn($qty);
        $this->stockItem->method('getBackOrders')
            ->willReturn($backOrders);
        $this->stockItem->method('getMinQty')
            ->willReturn($minQty);
        $this->stockItem->method('getManageStock')
            ->willReturn($manageStock);

        $result = $this->model->verifyStock($this->stockItem);

        self::assertEquals($expected, $result);
    }

    /**
     * StockItem data provider.
     *
     * @return array
     */
    public function stockItemDataProvider(): array
    {
        return [
            'qty_is_null_manage_stock_on' => [
                'qty' => null,
                'backorders' => null,
                'min_qty' => null,
                'manage_stock' => 1,
                'expected' => false,
            ],
            'qty_reached_threshold_without_backorders' => [
                'qty' => 3,
                'backorders' => Stock::BACKORDERS_NO,
                'min_qty' => 3,
                'manage_stock' => 1,
                'expected' => false,
            ],
            'backorders_are_ininite' => [
                'qty' => -100,
                'backorders' => Stock::BACKORDERS_YES_NONOTIFY,
                'min_qty' => 0,
                'manage_stock' => 1,
                'expected' => true,
            ],
            'limited_backorders_and_qty_reached_threshold' => [
                'qty' => -100,
                'backorders' => Stock::BACKORDERS_YES_NONOTIFY,
                'min_qty' => -100,
                'manage_stock' => 1,
                'expected' => false,
            ],
            'qty_not_yet_reached_threshold_1' => [
                'qty' => -99,
                'backorders' => Stock::BACKORDERS_YES_NONOTIFY,
                'min_qty' => -100,
                'manage_stock' => 1,
                'expected' => true,
            ],
            'qty_not_yet_reached_threshold_2' => [
                'qty' => 1,
                'backorders' => Stock::BACKORDERS_NO,
                'min_qty' => 0,
                'manage_stock' => 1,
                'expected' => true,
            ],
        ];
    }

    /**
     * Tests checkQty method.
     *
     * @return void
     *
     * @dataProvider stockItemAndQtyDataProvider
     * @covers \Magento\CatalogInventory\Model\StockStateProvider::verifyStock
     */
    public function testCheckQty(
        bool $manageStock,
        int $qty,
        int $minQty,
        int $backOrders,
        int $orderQty,
        bool $expected
    ): void {
        $this->stockItem->method('getManageStock')
            ->willReturn($manageStock);
        $this->stockItem->method('getQty')
            ->willReturn($qty);
        $this->stockItem->method('getMinQty')
            ->willReturn($minQty);
        $this->stockItem->method('getBackOrders')
            ->willReturn($backOrders);

        $result = $this->model->checkQty($this->stockItem, $orderQty);

        self::assertEquals($expected, $result);
    }

    /**
     * StockItem and qty data provider.
     *
     * @return array
     */
    public function stockItemAndQtyDataProvider(): array
    {
        return [
            'disabled_manage_stock' => [
                'manage_stock' => false,
                'qty' => 0,
                'min_qty' => 0,
                'backorders' => 0,
                'order_qty' => 0,
                'expected' => true,
            ],
            'infinite_backorders' => [
                'manage_stock' => true,
                'qty' => -100,
                'min_qty' => 0,
                'backorders' => Stock::BACKORDERS_YES_NONOTIFY,
                'order_qty' => 100,
                'expected' => true,
            ],
            'qty_reached_threshold' => [
                'manage_stock' => true,
                'qty' => -100,
                'min_qty' => -100,
                'backorders' => Stock::BACKORDERS_YES_NOTIFY,
                'order_qty' => 1,
                'expected' => false,
            ],
            'qty_yet_not_reached_threshold' => [
                'manage_stock' => true,
                'qty' => -100,
                'min_qty' => -100,
                'backorders' => Stock::BACKORDERS_YES_NOTIFY,
                'order_qty' => 1,
                'expected' => false,
            ]
        ];
    }

    /**
     * Tests checkQty method when check is not applicable.
     *
     * @return void
     */
    public function testCheckQtyWhenCheckIsNotApplicable(): void
    {
        $model = $this->objectManager->getObject(StockStateProvider::class, ['qtyCheckApplicable' => false]);

        $result = $model->checkQty($this->stockItem, 3);

        self::assertTrue($result);
    }
}
