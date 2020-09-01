<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Product\Plugin;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Product\Plugin\UpdateQuoteItems;
use Magento\Quote\Model\ResourceModel\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateQuoteItemsTest extends TestCase
{
    /**
     * @var UpdateQuoteItems
     */
    private $model;

    /**
     * @var MockObject|Quote
     */
    private $quoteResource;

    protected function setUp(): void
    {
        $this->quoteResource = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new UpdateQuoteItems($this->quoteResource);
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
        $productResourceMock = $this->createMock(Product::class);
        $productMock = $this->getMockBuilder(AbstractModel::class)
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
