<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote\Item\RelatedProducts;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Magento\Quote\Model\Quote\Item\RelatedProducts::class)]
class RelatedProductsTest extends TestCase
{
    use MockCreationTrait;

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
     */
    #[DataProvider('getRelatedProductIdsDataProvider')]
    public function testGetRelatedProductIds($optionValue, $productId, $expectedResult)
    {
        $quoteItemMock = $this->createMock(Item::class);
        $itemOptionMock = $this->createPartialMockWithReflection(
            Option::class,
            ['getValue', 'getProductId', '__wakeup']
        );

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

        $itemOptionMock->method('getProductId')->willReturn($productId);

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
    public static function getRelatedProductIdsDataProvider()
    {
        return [
            ['optionValue' => 'type1', 'productId' => 123, 'expectedResult' => [123]],
            ['optionValue' => 'other_type', 'productId' => 123, 'expectedResult' => []],
            ['optionValue' => 'type1', 'productId' => null, 'expectedResult' => []],
            ['optionValue' => 'other_type', 'productId' => false, 'expectedResult' => []]
        ];
    }

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
