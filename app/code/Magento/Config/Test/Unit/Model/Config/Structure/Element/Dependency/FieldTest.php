<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Element\Dependency;

use Magento\Config\Model\Config\Structure\Element\Dependency\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    /**#@+
     * SUT values
     */
    private const SIMPLE_VALUE = 'someValue';

    private const EMPTY_VALUE = '';

    private const COMPLEX_VALUE1 = 'value_1';

    private const COMPLEX_VALUE2 = 'value_2';

    private const COMPLEX_VALUE3 = 'value_3';

    /**#@-*/

    /**
     * Field prefix
     */
    private const PREFIX = 'prefix_';

    /**
     * Get simple data for creating SUT
     *
     * @return array
     */
    protected static function _getSimpleData()
    {
        return ['value' => self::SIMPLE_VALUE, 'dependPath' => ['section_2', 'group_3', 'field_4']];
    }

    /**
     * Get complex data for creating SUT
     *
     * @return array
     */
    protected static function _getComplexData()
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
        return new Field($data, self::PREFIX);
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

    /**
     * @return array
     */
    public static function dataProvider()
    {
        return [
            [self::_getSimpleData(), true],
            [self::_getSimpleData(), false],
            [self::_getComplexData(), true],
            [self::_getComplexData(), false]
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

    /**
     * @return array
     */
    public static function isValueSatisfyDataProvider()
    {
        return [
            [self::_getSimpleData(), true, self::SIMPLE_VALUE, false],
            [self::_getSimpleData(), false, self::SIMPLE_VALUE, true],
            [self::_getSimpleData(), true, self::COMPLEX_VALUE1, true],
            [self::_getSimpleData(), false, self::COMPLEX_VALUE2, false],
            [self::_getComplexData(), true, self::COMPLEX_VALUE1, false],
            [self::_getComplexData(), false, self::COMPLEX_VALUE2, true],
            [self::_getComplexData(), true, self::SIMPLE_VALUE, true],
            [self::_getComplexData(), false, self::SIMPLE_VALUE, false]
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

    /**
     * @return array
     */
    public static function getValuesDataProvider()
    {
        $complexDataValues = [self::COMPLEX_VALUE1, self::COMPLEX_VALUE2, self::COMPLEX_VALUE3];
        return [
            [self::_getSimpleData(), true, [self::SIMPLE_VALUE]],
            [self::_getSimpleData(), false, [self::SIMPLE_VALUE]],
            [self::_getSimpleEmptyData(), false, [static::EMPTY_VALUE]],
            [self::_getComplexData(), true, $complexDataValues],
            [self::_getComplexData(), false, $complexDataValues]
        ];
    }

    /**
     * Providing a field data with no field value
     *
     * @return array
     */
    protected static function _getSimpleEmptyData(): array
    {
        return ['dependPath' => ['section_2', 'group_3', 'field_4']];
    }
}
