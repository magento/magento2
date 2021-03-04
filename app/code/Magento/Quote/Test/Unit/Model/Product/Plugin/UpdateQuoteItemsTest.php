<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Product\Plugin;

class UpdateQuoteItemsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Product\Plugin\UpdateQuoteItems
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Model\ResourceModel\Quote
     */
    private $quoteResource ;

    protected function setUp(): void
    {
        $this->quoteResource = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Quote\Model\Product\Plugin\UpdateQuoteItems($this->quoteResource);
    }

    /**
     * @dataProvider aroundUpdateDataProvider
     * @param int $originalPrice
     * @param int $newPrice
     * @param bool $callMethod
     * @param bool $tierPriceChanged
     */
    public function testAfterUpdate($originalPrice, $newPrice, $callMethod, $tierPriceChanged = false)
    {
        $productResourceMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productMock = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrigData', 'getPrice', 'getId', 'getData'])
            ->getMockForAbstractClass();
        $productId = 1;
        $productMock->expects($this->any())->method('getOrigData')->with('price')->willReturn($originalPrice);
        $productMock->expects($this->any())->method('getPrice')->willReturn($newPrice);
        $productMock->expects($this->any())->method('getId')->willReturn($productId);
        $productMock->expects($this->any())->method('getData')->willReturn($tierPriceChanged);
        $this->quoteResource->expects($this->$callMethod())->method('markQuotesRecollect')->with($productId);
        $result = $this->model->afterSave($productResourceMock, $productResourceMock, $productMock);
        $this->assertEquals($result, $productResourceMock);
    }

    /**
     * @return array
     */
    public function aroundUpdateDataProvider()
    {
        return [
            [10, 20, 'once'],
            [null, 10, 'never'],
            [10, 10, 'never'],
            [10, 10, 'once', true],
        ];
    }
}
