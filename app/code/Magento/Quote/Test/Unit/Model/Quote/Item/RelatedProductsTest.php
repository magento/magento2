<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote\Item\RelatedProducts;
use PHPUnit\Framework\TestCase;

class RelatedProductsTest extends TestCase
{
    /**
     * @var RelatedProducts
     */
    protected $model;

    /**
     * @var array
     */
    protected $relatedProductTypes;

    protected function setUp(): void
    {
        $this->relatedProductTypes = ['type1', 'type2', 'type3'];
        $this->model = new RelatedProducts($this->relatedProductTypes);
    }

    /**
     * @param string $optionValue
     * @param int|bool $productId
     * @param array $expectedResult
     *
     * @covers \Magento\Quote\Model\Quote\Item\RelatedProducts::getRelatedProductIds
     * @dataProvider getRelatedProductIdsDataProvider
     */
    public function testGetRelatedProductIds($optionValue, $productId, $expectedResult)
    {
        $quoteItemMock = $this->createMock(Item::class);
        $itemOptionMock = $this->getMockBuilder(Option::class)
            ->addMethods(['getProductId'])
            ->onlyMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $quoteItemMock->expects(
            $this->once()
        )->method(
            'getOptionByCode'
        )->with(
            'product_type'
        )->willReturn(
            $itemOptionMock
        );

        $itemOptionMock->expects($this->once())->method('getValue')->willReturn($optionValue);

        $itemOptionMock->expects($this->any())->method('getProductId')->willReturn($productId);

        $this->assertEquals($expectedResult, $this->model->getRelatedProductIds([$quoteItemMock]));
    }

    /*
     * Data provider for testGetRelatedProductIds
     *
     * @return array
     */
    /**
     * @return array
     */
    public function getRelatedProductIdsDataProvider()
    {
        return [
            ['optionValue' => 'type1', 'productId' => 123, 'expectedResult' => [123]],
            ['optionValue' => 'other_type', 'productId' => 123, 'expectedResult' => []],
            ['optionValue' => 'type1', 'productId' => null, 'expectedResult' => []],
            ['optionValue' => 'other_type', 'productId' => false, 'expectedResult' => []]
        ];
    }

    /**
     * @covers \Magento\Quote\Model\Quote\Item\RelatedProducts::getRelatedProductIds
     */
    public function testGetRelatedProductIdsNoOptions()
    {
        $quoteItemMock = $this->createMock(Item::class);

        $quoteItemMock->expects(
            $this->once()
        )->method(
            'getOptionByCode'
        )->with(
            'product_type'
        )->willReturn(
            new \stdClass()
        );

        $this->assertEquals([], $this->model->getRelatedProductIds([$quoteItemMock]));
    }
}
