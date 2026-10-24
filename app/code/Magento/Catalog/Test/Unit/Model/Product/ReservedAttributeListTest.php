<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\ReservedAttributeList;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReservedAttributeList::class)]
class ReservedAttributeListTest extends TestCase
{
    /**
     * @var ReservedAttributeList
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new ReservedAttributeList(
            Product::class,
            ['some_value'],
            ['some_attribute']
        );
    }

    #[DataProvider('dataProvider')]
    public function testIsReservedAttribute($isUserDefined, $attributeCode, $expected)
    {
        $attribute = $this->createPartialMock(
            Attribute::class,
            ['getIsUserDefined', 'getAttributeCode', '__sleep']
        );

        $attribute->expects($this->once())->method('getIsUserDefined')->willReturn($isUserDefined);
        $attribute->method('getAttributeCode')->willReturn($attributeCode);

        $this->assertEquals($expected, $this->model->isReservedAttribute($attribute));
    }

    /**
     * @return array
     */
    public static function dataProvider()
    {
        return [
            [false, 'some_code', false],
            [true, 'some_value', true],
            [true, 'name', true],
            [true, 'price', true],
            [true, 'category_id', true],
            [true, 'some_code', false],
        ];
    }
}
