<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InventoryReservations\Model\SnakeToCamelCaseConverter;
use PHPUnit\Framework\TestCase;

class SnakeToCamelCaseConverterTest extends TestCase
{
    /**
     * @var SnakeToCamelCaseConverter
     */
    private $snakeToCamelCaseConverter;

    protected function setUp()
    {
        $this->snakeToCamelCaseConverter = (new ObjectManager($this))->getObject(SnakeToCamelCaseConverter::class);
    }

    /**
     * @dataProvider getElementsToConvert
     * @param array $givenElements
     * @param array $expectedElements
     */
    public function testArrayElementConversion(array $givenElements, array $expectedElements)
    {
        self::assertEquals($expectedElements, $this->snakeToCamelCaseConverter->convert($givenElements));
    }

    /**
     * @return array
     */
    public function getElementsToConvert(): array
    {
        return [
            'with_empty_data' => [[], []],
            'to_lowercase' => [
                ['one', 'Two', 'THREE'],
                ['one', 'two', 'three']
            ],
            'underscore_to_camelcase' => [
                ['my_element_one', 'My_Element_Two', 'MY_ELEMENT_THREE'],
                ['myElementOne', 'myElementTwo', 'myElementThree']
            ],
        ];
    }
}
