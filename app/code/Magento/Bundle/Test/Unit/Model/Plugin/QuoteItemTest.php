<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Plugin;

use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Catalog\Model\Product;

class QuoteItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Product
     */
    private $productMock;

    /** @var \Magento\Bundle\Model\Plugin\QuoteItem */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|AbstractItem */
    protected $quoteItemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|OrderItemInterface */
    protected $orderItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ToOrderItem
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->orderItemMock = $this->getMockForAbstractClass(
            OrderItemInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getProductOptions', 'setProductOptions']
        );
        $this->quoteItemMock = $this->getMockForAbstractClass(
            AbstractItem::class,
            [],
            '',
            false,
            false,
            true,
            ['getProduct']
        );
        $this->subjectMock = $this->createMock(ToOrderItem::class);
        $this->productMock = $this->createMock(Product::class);
        $this->model = new \Magento\Bundle\Model\Plugin\QuoteItem();
    }

    public function testAroundItemToOrderItemPositive()
    {
        $attributeValue = 'test_value';
        $productOptions = [
            'option_1' => 'value_1',
            'option_2' => 'value_2'
        ];
        $expectedOptions = $productOptions + ['bundle_selection_attributes' => $attributeValue];

        $bundleAttribute = $this->createMock(\Magento\Catalog\Model\Product\Configuration\Item\Option::class);
        $bundleAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn($attributeValue);

        $this->productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_attributes')
            ->willReturn($bundleAttribute);
        $this->quoteItemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);

        $this->orderItemMock->expects($this->once())->method('getProductOptions')->willReturn($productOptions);
        $this->orderItemMock->expects($this->once())->method('setProductOptions')->with($expectedOptions);

        $orderItem = $this->model->afterConvert($this->subjectMock, $this->orderItemMock, $this->quoteItemMock);
        $this->assertSame($this->orderItemMock, $orderItem);
    }

    public function testAroundItemToOrderItemNegative()
    {
        $this->productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_attributes')->willReturn(false);

        $this->quoteItemMock->expects($this->once())->method('getProduct')
            ->willReturn($this->productMock);
        $this->orderItemMock->expects($this->never())->method('setProductOptions');

        $orderItem = $this->model->afterConvert($this->subjectMock, $this->orderItemMock, $this->quoteItemMock);
        $this->assertSame($this->orderItemMock, $orderItem);
    }
}
