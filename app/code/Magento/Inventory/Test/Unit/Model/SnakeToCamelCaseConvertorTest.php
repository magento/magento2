<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Inventory\Model\SnakeToCamelCaseConvertor;
use PHPUnit\Framework\TestCase;

class SnakeToCamelCaseConvertorTest extends TestCase
{
    /**
     * @var SnakeToCamelCaseConvertor
     */
    private $snakeToCamelCaseConvertor;

    protected function setUp()
    {
        $this->snakeToCamelCaseConvertor = (new ObjectManager($this))->getObject(SnakeToCamelCaseConvertor::class);
    }

    /**
     * @dataProvider getElementsToConvert
     * @param array $givenElements
     * @param array $expectedElements
     */
    public function testArrayElementConversion(array $givenElements, array $expectedElements)
    {
        self::assertEquals($expectedElements, $this->snakeToCamelCaseConvertor->convert($givenElements));
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
