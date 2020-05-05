<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Plugin;

use Magento\Bundle\Model\Plugin\QuoteItem;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteItemTest extends TestCase
{
    /**
     * @var MockObject|Product
     */
    private $productMock;

    /** @var QuoteItem */
    protected $model;

    /** @var MockObject|AbstractItem */
    protected $quoteItemMock;

    /** @var MockObject|OrderItemInterface */
    protected $orderItemMock;

    /**
     * @var MockObject|ToOrderItem
     */
    protected $subjectMock;

    protected function setUp(): void
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
        $this->model = new QuoteItem();
    }

    public function testAroundItemToOrderItemPositive()
    {
        $attributeValue = 'test_value';
        $productOptions = [
            'option_1' => 'value_1',
            'option_2' => 'value_2'
        ];
        $expectedOptions = $productOptions + ['bundle_selection_attributes' => $attributeValue];

        $bundleAttribute = $this->createMock(Option::class);
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
