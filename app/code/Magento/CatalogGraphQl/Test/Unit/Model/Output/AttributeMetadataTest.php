<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Output;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogGraphQl\Model\Output\AttributeMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AttributeMetadataTest extends TestCase
{
    /**
     * @param string $isFilterable
     * @param bool $expected
     */
    #[DataProvider('isFilterableDataProvider')]
    public function testIsFilterable(string $isFilterable, bool $expected): void
    {
        $attribute = (new ReflectionClass(Attribute::class))->newInstanceWithoutConstructor();
        $attribute->setData('is_filterable', $isFilterable);

        $result = (new AttributeMetadata('catalog_product'))->execute($attribute, 'catalog_product', 1);

        $this->assertSame($expected, $result['is_filterable']);
    }

    /**
     * @return array
     */
    public static function isFilterableDataProvider(): array
    {
        return [
            'no' => ['0', false],
            'filterable with results' => ['1', true],
            'filterable no results' => ['2', true],
        ];
    }
}
