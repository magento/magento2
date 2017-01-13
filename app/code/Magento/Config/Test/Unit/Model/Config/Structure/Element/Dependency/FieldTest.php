<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Element\Dependency;

class FieldTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * SUT values
     */
    const SIMPLE_VALUE = 'someValue';

    const COMPLEX_VALUE1 = 'value_1';

    const COMPLEX_VALUE2 = 'value_2';

    const COMPLEX_VALUE3 = 'value_3';

    /**#@-*/

    /**
     * Field prefix
     */
    const PREFIX = 'prefix_';

    /**
     * Get simple data for creating SUT
     *
     * @return array
     */
    protected function _getSimpleData()
    {
        return ['value' => self::SIMPLE_VALUE, 'dependPath' => ['section_2', 'group_3', 'field_4']];
    }

    /**
     * Get complex data for creating SUT
     *
     * @return array
     */
    protected function _getComplexData()
    {
        return [
            'value' => self::COMPLEX_VALUE1 . ',' . self::COMPLEX_VALUE2 . ',' . self::COMPLEX_VALUE3,
            'separator' => ',',
            'dependPath' => ['section_5', 'group_6', 'group_7', 'field_8']
        ];
    }

    /**
     * Get SUT
     *
     * @param array $data
     * @param bool $isNegative
     * @return \Magento\Config\Model\Config\Structure\Element\Dependency\Field
     */
    protected function _getFieldObject($data, $isNegative)
    {
        if ($isNegative) {
            $data['negative'] = '1';
        }
        return new \Magento\Config\Model\Config\Structure\Element\Dependency\Field($data, self::PREFIX);
    }

    /**
     * @param array $data
     * @param bool $isNegative
     * @dataProvider dataProvider
     */
    public function testGetId($data, $isNegative)
    {
        $fieldObject = $this->_getFieldObject($data, $isNegative);
        $fieldId = self::PREFIX . array_pop($data['dependPath']);
        $data['dependPath'][] = $fieldId;
        $expected = implode('_', $data['dependPath']);
        $this->assertEquals($expected, $fieldObject->getId());
    }

    /**
     * @param array $data
     * @param bool $isNegative
     * @dataProvider dataProvider
     */
    public function testIsNegative($data, $isNegative)
    {
        $this->assertEquals($isNegative, $this->_getFieldObject($data, $isNegative)->isNegative());
    }

    public function dataProvider()
    {
        return [
            [$this->_getSimpleData(), true],
            [$this->_getSimpleData(), false],
            [$this->_getComplexData(), true],
            [$this->_getComplexData(), false]
        ];
    }

    /**
     * @param array $data
     * @param bool $isNegative
     * @param string $value
     * @param bool $expected
     * @dataProvider isValueSatisfyDataProvider
     */
    public function testIsValueSatisfy($data, $isNegative, $value, $expected)
    {
        $this->assertEquals($expected, $this->_getFieldObject($data, $isNegative)->isValueSatisfy($value));
    }

    public function isValueSatisfyDataProvider()
    {
        return [
            [$this->_getSimpleData(), true, self::SIMPLE_VALUE, false],
            [$this->_getSimpleData(), false, self::SIMPLE_VALUE, true],
            [$this->_getSimpleData(), true, self::COMPLEX_VALUE1, true],
            [$this->_getSimpleData(), false, self::COMPLEX_VALUE2, false],
            [$this->_getComplexData(), true, self::COMPLEX_VALUE1, false],
            [$this->_getComplexData(), false, self::COMPLEX_VALUE2, true],
            [$this->_getComplexData(), true, self::SIMPLE_VALUE, true],
            [$this->_getComplexData(), false, self::SIMPLE_VALUE, false]
        ];
    }

    /**
     * @param array $data
     * @param bool $isNegative
     * @param array $expected
     * @dataProvider getValuesDataProvider
     */
    public function testGetValues($data, $isNegative, $expected)
    {
        $this->assertEquals($expected, $this->_getFieldObject($data, $isNegative)->getValues());
    }

    public function getValuesDataProvider()
    {
        $complexDataValues = [self::COMPLEX_VALUE1, self::COMPLEX_VALUE2, self::COMPLEX_VALUE3];
        return [
            [$this->_getSimpleData(), true, [self::SIMPLE_VALUE]],
            [$this->_getSimpleData(), false, [self::SIMPLE_VALUE]],
            [$this->_getComplexData(), true, $complexDataValues],
            [$this->_getComplexData(), false, $complexDataValues]
        ];
    }
}
