<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Structure\Element\Dependency;

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
        return array('value' => self::SIMPLE_VALUE, 'dependPath' => array('section_2', 'group_3', 'field_4'));
    }

    /**
     * Get complex data for creating SUT
     *
     * @return array
     */
    protected function _getComplexData()
    {
        return array(
            'value' => self::COMPLEX_VALUE1 . ',' . self::COMPLEX_VALUE2 . ',' . self::COMPLEX_VALUE3,
            'separator' => ',',
            'dependPath' => array('section_5', 'group_6', 'group_7', 'field_8')
        );
    }

    /**
     * Get SUT
     *
     * @param array $data
     * @param bool $isNegative
     * @return \Magento\Backend\Model\Config\Structure\Element\Dependency\Field
     */
    protected function _getFieldObject($data, $isNegative)
    {
        if ($isNegative) {
            $data['negative'] = '1';
        }
        return new \Magento\Backend\Model\Config\Structure\Element\Dependency\Field($data, self::PREFIX);
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
        return array(
            array($this->_getSimpleData(), true),
            array($this->_getSimpleData(), false),
            array($this->_getComplexData(), true),
            array($this->_getComplexData(), false)
        );
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
        return array(
            array($this->_getSimpleData(), true, self::SIMPLE_VALUE, false),
            array($this->_getSimpleData(), false, self::SIMPLE_VALUE, true),
            array($this->_getSimpleData(), true, self::COMPLEX_VALUE1, true),
            array($this->_getSimpleData(), false, self::COMPLEX_VALUE2, false),
            array($this->_getComplexData(), true, self::COMPLEX_VALUE1, false),
            array($this->_getComplexData(), false, self::COMPLEX_VALUE2, true),
            array($this->_getComplexData(), true, self::SIMPLE_VALUE, true),
            array($this->_getComplexData(), false, self::SIMPLE_VALUE, false)
        );
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
        $complexDataValues = array(self::COMPLEX_VALUE1, self::COMPLEX_VALUE2, self::COMPLEX_VALUE3);
        return array(
            array($this->_getSimpleData(), true, array(self::SIMPLE_VALUE)),
            array($this->_getSimpleData(), false, array(self::SIMPLE_VALUE)),
            array($this->_getComplexData(), true, $complexDataValues),
            array($this->_getComplexData(), false, $complexDataValues)
        );
    }
}
