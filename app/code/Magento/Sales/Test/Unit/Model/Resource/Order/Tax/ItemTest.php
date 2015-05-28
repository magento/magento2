<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Resource\Order\Tax;

/**
 * Class ItemTest
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Tax\Item
     */
    protected $taxItem;

    /**
     * Initialization
     */
    protected function setUp()
    {
        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [],
            [],
            '',
            false
        );
        $this->appResourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->adapterMock));
        $this->appResourceMock->expects($this->any())->method('getTableName')->willReturnArgument(0);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->taxItem = $objectManager->getObject(
            'Magento\Sales\Model\Resource\Order\Tax\Item',
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
        $select = $this->getMock(
            'Magento\Framework\DB\Select',
            [],
            [],
            '',
            false
        );
        $this->adapterMock->expects($this->once())->method('select')->willReturn($select);
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
        $this->adapterMock->expects($this->once())->method('fetchAll')->with($select)->willReturn($taxItems);
        $this->assertEquals($taxItems, $this->taxItem->getTaxItemsByOrderId($orderId));
    }
}
