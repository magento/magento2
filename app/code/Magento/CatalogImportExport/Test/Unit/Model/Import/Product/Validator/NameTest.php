<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Name;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    /**
     * @var SkuStorage|MockObject
     */
    private SkuStorage $skuStorage;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->skuStorage = $this->createMock(SkuStorage::class);
    }

    /**
     * @param $expected
     * @param $value
     * @return void
     * @throws Exception
     */
    #[DataProvider('getRowData')]
    public function testIsValid($expected, $value): void
    {
        $this->skuStorage->method('has')->willReturn(false);
        $nameValidator = new Name($this->skuStorage);
        $context = $this->createMock(Product::class);
        $context->method('getEmptyAttributeValueConstant')->willReturn('|');
        $context->method('retrieveMessageTemplate')->willReturn('%s error %s');
        $nameValidator->init($context);
        $this->assertSame($expected, $nameValidator->isValid($value));
    }

    /**
     * @return array[]
     */
    public static function getRowData(): array
    {
        return [
            [
                false,
                ['name' => null, 'store_view_code' => '', 'sku' => 'sku']
            ],
            [
                true,
                ['name' => 'anything goes here', 'store_view_code' => 'en', 'sku' => 'sku']
            ],
            [
                false,
                ['name' => null, 'store_view_code' => 'en', 'sku' => 'sku']
            ],
        ];
    }
}
