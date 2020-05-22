<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Product\Sold\Collection;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Reports\Model\ResourceModel\Product\Sold\Collection;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify data collection class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    protected $adapterMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var Collection;
     */
    protected $collection;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->selectMock = $this->createMock(Select::class);
        $this->adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->collection = $this->getMockBuilder(Collection::class)
            ->setMethods([
                'getSelect',
                'getConnection',
                'getTable'
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Verify get select count sql.
     *
     * @return void
     */
    public function testGetSelectCountSql(): void
    {
        $this->collection->expects($this->atLeastOnce())
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('reset')
            ->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))
            ->method('columns')
            ->willReturnSelf();
        $this->selectMock->expects($this->at(6))
            ->method('columns')
            ->with('COUNT(DISTINCT main_table.entity_id)');
        $this->selectMock->expects($this->at(7))
            ->method('reset')
            ->with(Select::COLUMNS);
        $this->selectMock->expects($this->at(8))
            ->method('columns')
            ->with('COUNT(DISTINCT order_items.item_id)');

        $this->assertEquals($this->selectMock, $this->collection->getSelectCountSql());
    }

    /**
     * Verify add ordered qty.
     *
     * @return void
     */
    public function testAddOrderedQty(): void
    {
        $this->collection->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->adapterMock);
        $this->adapterMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with('order')
            ->willReturn('sales_order');
        $this->adapterMock->expects($this->once())
            ->method('quoteInto')
            ->with('sales_order.state <> ?', Order::STATE_CANCELED)
            ->willReturn('');
        $this->collection->expects($this->atLeastOnce())
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $this->collection->expects($this->exactly(2))
            ->method('getTable')
            ->withConsecutive(
                ['sales_order_item'],
                ['sales_order']
            )->willReturnOnConsecutiveCalls(
                'sales_order_item',
                'sales_order'
            );
        $this->selectMock->expects($this->atLeastOnce())
            ->method('reset')
            ->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())
            ->method('from')
            ->with(
                [ 'order_items' => 'sales_order_item'],
                [
                    'ordered_qty' => 'order_items.qty_ordered',
                    'order_items_name' => 'order_items.name',
                    'order_items_sku' => 'order_items.sku'
                ]
            )
            ->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())
            ->method('joinInner')
            ->with(
                ['order' => 'sales_order'],
                'sales_order.entity_id = order_items.order_id AND ',
                []
            )
            ->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())
            ->method('where')
            ->with('order_items.parent_item_id IS NULL')
            ->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())
            ->method('having')
            ->with('order_items.qty_ordered > ?', 0)
            ->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())
            ->method('columns')
            ->with('SUM(order_items.qty_ordered) as ordered_qty')
            ->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())
            ->method('group')
            ->with('order_items.sku')
            ->willReturnSelf();

        $this->assertEquals($this->collection, $this->collection->addOrderedQty());
    }
}
