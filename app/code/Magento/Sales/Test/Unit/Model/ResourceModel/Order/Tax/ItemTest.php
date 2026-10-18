<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Tax;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemsPreloader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $appResourceMock;

    /**
     * @var Item
     */
    protected $taxItem;

    /**
     * @var ItemsPreloader|MockObject
     */
    private $itemsPreloaderMock;

    /**
     * Initialization
     */
    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->appResourceMock = $this->createMock(ResourceConnection::class);
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->appResourceMock->expects($this->any())->method('getTableName')->willReturnArgument(0);
        $this->itemsPreloaderMock = $this->createMock(ItemsPreloader::class);
        $objectManager = new ObjectManager($this);
        $this->taxItem = $objectManager->getObject(
            Item::class,
            [
                'resource' => $this->appResourceMock,
                'itemsPreloader' => $this->itemsPreloaderMock
            ]
        );
    }

    public function testGetTaxItemsByOrderId()
    {
        $orderId = 1;
        $taxItems = [
            [
                'tax_id' => 1,
                'tax_percent' => 5,
                'item_id' => 1,
                'taxable_item_type' => 4,
                'associated_item_id' => 1,
                'real_amount' => 12,
                'real_base_amount' => 12
            ]
        ];
        $select = $this->createMock(Select::class);
        $this->connectionMock->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->with(
            ['item' => 'sales_order_tax_item'],
            [
                'tax_id',
                'tax_percent',
                'item_id',
                'taxable_item_type',
                'associated_item_id',
                'real_amount',
                'real_base_amount',
            ]
        )->willReturnSelf();
        $select->expects($this->once())->method('join')->with(
            ['tax' => 'sales_order_tax'],
            'item.tax_id = tax.tax_id',
            ['code', 'title', 'order_id']
        )->willReturnSelf();
        $select->expects($this->once())->method('where')->with(
            'tax.order_id = ?',
            $orderId
        )->willReturnSelf();
        $this->connectionMock->expects($this->once())->method('fetchAll')->with($select)->willReturn($taxItems);
        $this->assertEquals($taxItems, $this->taxItem->getTaxItemsByOrderId($orderId));
    }

    public function testGetTaxItemsByOrderIdReturnsPreloadedRowsWithoutQuery(): void
    {
        $taxItems = [['tax_id' => 1, 'item_id' => 1, 'order_id' => 7]];
        $this->itemsPreloaderMock->expects($this->once())->method('take')->with(7)->willReturn($taxItems);
        $this->connectionMock->expects($this->never())->method('fetchAll');

        $this->assertSame($taxItems, $this->taxItem->getTaxItemsByOrderId('7'));
    }

    public function testGetTaxItemsByOrderIds(): void
    {
        $orderIds = [1, 2];
        $taxItems = [['tax_id' => 1, 'order_id' => 1], ['tax_id' => 2, 'order_id' => 2]];
        $select = $this->createMock(Select::class);
        $this->connectionMock->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->willReturnSelf();
        $select->expects($this->once())->method('join')->willReturnSelf();
        $select->expects($this->once())->method('where')->with('tax.order_id IN (?)', $orderIds)->willReturnSelf();
        $this->connectionMock->expects($this->once())->method('fetchAll')->with($select)->willReturn($taxItems);
        $this->itemsPreloaderMock->expects($this->never())->method('take');

        $this->assertSame($taxItems, $this->taxItem->getTaxItemsByOrderIds($orderIds));
    }
}
