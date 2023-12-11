<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Tax;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item;
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
        $objectManager = new ObjectManager($this);
        $this->taxItem = $objectManager->getObject(
            Item::class,
            [
                'resource' => $this->appResourceMock
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
}
