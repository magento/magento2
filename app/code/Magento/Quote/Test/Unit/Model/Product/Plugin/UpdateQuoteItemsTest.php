<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Product\Plugin;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Product\Plugin\UpdateQuoteItems;
use Magento\Quote\Model\ResourceModel\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Test\Unit\Helper\AbstractModelPriceTestHelper;

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
     * @param int $originalPrice
     * @param int $newPrice
     * @param bool $callMethod
     * @param bool $tierPriceChanged
     */
    #[DataProvider('aroundUpdateDataProvider')]
    public function testAfterUpdate($originalPrice, $newPrice, $callMethod, $tierPriceChanged = false)
    {
        $productResourceMock = $this->createMock(Product::class);
        $productMock = $this->getMockBuilder(AbstractModelPriceTestHelper::class)
            ->onlyMethods(['getOrigData', 'getId', 'getData', 'getPrice'])
            ->getMock();
        $productId = 1;
        $productMock->expects($this->any())->method('getOrigData')->with('price')->willReturn($originalPrice);
        $productMock->method('getPrice')->willReturn($newPrice);
        $productMock->method('getId')->willReturn($productId);
        $productMock->method('getData')->willReturn($tierPriceChanged);
        $this->quoteResource->expects($this->$callMethod())->method('markQuotesRecollect')->with($productId);
        $result = $this->model->afterSave($productResourceMock, $productResourceMock, $productMock);
        $this->assertEquals($result, $productResourceMock);
    }

    /**
     * @return array
     */
    public static function aroundUpdateDataProvider()
    {
        return [
            [10, 20, 'once'],
            [null, 10, 'never'],
            [10, 10, 'never'],
            [10, 10, 'once', true],
        ];
    }
}
