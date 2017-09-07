<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Inventory\Model\SnakeToCamelCaseConvertor;

class SnakeToCamelCaseConvertorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Return an instance of SnakeToCamelCaseConvertor
     *
     * @return SnakeToCamelCaseConvertor
     */
    private function createSnakeToCamelCaseConvertor(): SnakeToCamelCaseConvertor
    {
        return (new ObjectManager($this))->getObject(SnakeToCamelCaseConvertor::class);
    }

    /**
     * @dataProvider getElementsToConvert
     */
    public function testArrayElementConversion(array $givenElements, array $expectedElements)
    {
        $convertor = $this->createSnakeToCamelCaseConvertor();
        $this->assertEquals($expectedElements, $convertor->convert($givenElements));
    }

    /**
     * @return array
     */
    public function getElementsToConvert(): array
    {
        return [
            [[],[]],
            [['one', 'Two', 'THREE'],['one', 'two', 'three']],
            [['my_element_one', 'My_Element_Two', 'MY_ELEMENT_THREE'],['myElementOne', 'myElementTwo', 'myElementThree']],
        ];
    }
}
