<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Price;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    private Price $price;

    protected function setUp(): void
    {
        $searchCriteria = $this->createMock(SearchCriteria::class);

        $searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $searchCriteriaBuilder->method('create')->willReturn($searchCriteria);

        $priceFields = ['price', 'special_price', 'cost', 'map_price', 'minimal_price', 'msrp_price', 'msrp'];
        $attributes = array_map(function (string $code): MockObject {
            $attr = $this->createMock(ProductAttributeInterface::class);
            $attr->method('getAttributeCode')->willReturn($code);
            return $attr;
        }, $priceFields);

        $searchResult = $this->createMock(ProductAttributeSearchResultsInterface::class);
        $searchResult->method('getItems')->willReturn($attributes);

        $attributeRepository = $this->createMock(ProductAttributeRepositoryInterface::class);
        $attributeRepository->method('getList')->willReturn($searchResult);

        $this->price = new Price($attributeRepository, $searchCriteriaBuilder);

        $contextStub = $this->createMock(Product::class);
        $contextStub->method('getEmptyAttributeValueConstant')
            ->willReturn(Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT);
        $this->price->init($contextStub);
    }

    /**
     * @param bool $expectedResult
     * @param array<string, mixed> $value
     */
    #[DataProvider('isValidDataProvider')]
    public function testIsValid(bool $expectedResult, array $value): void
    {
        $this->assertSame($expectedResult, $this->price->isValid($value));
    }

    /**
     * @return array<int, array{0: bool, 1: array<string, mixed>}>
     */
    public static function isValidDataProvider(): array
    {
        $empty = Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT;

        return [
            'positive_price_only' => [true, ['price' => '10.5']],
            'zero_price' => [true, ['price' => '0']],
            'zero_price_int' => [true, ['price' => 0]],
            'price_unset_other_keys' => [true, ['sku' => 'test']],
            'empty_string_price_skipped' => [true, ['price' => '']],
            'null_price_skipped' => [true, ['price' => null]],
            'empty_constant_price_skipped' => [true, ['price' => $empty]],
            'whitespace_only_price_skipped' => [true, ['price' => ' ']],
            'whitespace_only_special_price_skipped' => [true, ['special_price' => '   ']],
            'negative_price' => [false, ['price' => '-1']],
            'negative_price_float' => [false, ['price' => -150]],
            'negative_special_price_with_valid_price' => [false, ['price' => '10', 'special_price' => '-0.01']],
            'negative_cost_with_valid_price' => [false, ['price' => '10', 'cost' => -5]],
            'negative_map_price' => [false, ['map_price' => '-2']],
            'negative_msrp_price' => [false, ['msrp_price' => '-3']],
            'negative_msrp' => [false, ['msrp' => '-4']],
            'negative_minimal_price' => [false, ['minimal_price' => '-1']],
            'non_numeric_price' => [true, ['price' => 'abc']],
            'non_numeric_special_price' => [true, ['price' => '1', 'special_price' => 'not-a-number']],
        ];
    }

    public function testMessagesContainFieldNameWhenNegativePrice(): void
    {
        $this->assertFalse($this->price->isValid(['price' => '-10']));
        $messages = $this->price->getMessages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('price', $messages[0]);
    }

    public function testAllNegativeFieldsReportedIndividually(): void
    {
        $this->assertFalse($this->price->isValid(['price' => '-1', 'special_price' => '-2']));
        $messages = $this->price->getMessages();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString('price', $messages[0]);
        $this->assertStringContainsString('special_price', $messages[1]);
    }

    public function testNoMessagesWhenValid(): void
    {
        $this->assertTrue($this->price->isValid(['price' => '99']));
        $this->assertSame([], $this->price->getMessages());
    }
}
