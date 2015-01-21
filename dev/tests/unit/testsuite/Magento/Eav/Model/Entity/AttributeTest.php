<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Attribute model to be tested
     * @var \Magento\Eav\Model\Entity\Attribute|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMock('Magento\Eav\Model\Entity\Attribute', ['__wakeup'], [], '', false);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @param string $givenFrontendInput
     * @param string $expectedBackendType
     * @dataProvider dataGetBackendTypeByInput
     */
    public function testGetBackendTypeByInput($givenFrontendInput, $expectedBackendType)
    {
        $this->assertEquals($expectedBackendType, $this->_model->getBackendTypeByInput($givenFrontendInput));
    }

    public static function dataGetBackendTypeByInput()
    {
        return [
            ['unrecognized-frontend-input', null],
            ['text', 'varchar'],
            ['gallery', 'varchar'],
            ['media_image', 'varchar'],
            ['multiselect', 'varchar'],
            ['image', 'text'],
            ['textarea', 'text'],
            ['date', 'datetime'],
            ['select', 'int'],
            ['boolean', 'int'],
            ['price', 'decimal'],
            ['weight', 'decimal']
        ];
    }

    /**
     * @param string $givenFrontendInput
     * @param string $expectedDefaultValue
     * @dataProvider dataGetDefaultValueByInput
     */
    public function testGetDefaultValueByInput($givenFrontendInput, $expectedDefaultValue)
    {
        $this->assertEquals($expectedDefaultValue, $this->_model->getDefaultValueByInput($givenFrontendInput));
    }

    public static function dataGetDefaultValueByInput()
    {
        return [
            ['unrecognized-frontend-input', ''],
            ['select', ''],
            ['gallery', ''],
            ['media_image', ''],
            ['multiselect', null],
            ['text', 'default_value_text'],
            ['price', 'default_value_text'],
            ['image', 'default_value_text'],
            ['weight', 'default_value_text'],
            ['textarea', 'default_value_textarea'],
            ['date', 'default_value_date'],
            ['boolean', 'default_value_yesno']
        ];
    }

    /**
     * @param array|null $sortWeights
     * @param float $expected
     * @dataProvider getSortWeightDataProvider
     */
    public function testGetSortWeight($sortWeights, $expected)
    {
        $setId = 123;
        $this->_model->setAttributeSetInfo([$setId => $sortWeights]);
        $this->assertEquals($expected, $this->_model->getSortWeight($setId));
    }

    /**
     * @return array
     */
    public function getSortWeightDataProvider()
    {
        return [
            'empty set info' => ['sortWeights' => null, 'expectedWeight' => 0],
            'no group sort' => ['sortWeights' => ['sort' => 5], 'expectedWeight' => 0.0005],
            'no sort' => ['sortWeights' => ['group_sort' => 7], 'expectedWeight' => 7000],
            'group sort and sort' => [
                'sortWeights' => ['group_sort' => 7, 'sort' => 5],
                'expectedWeight' => 7000.0005,
            ]
        ];
    }
}
