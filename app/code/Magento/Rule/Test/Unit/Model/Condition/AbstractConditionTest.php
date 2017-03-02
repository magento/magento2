<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model\Condition;

class AbstractConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractCondition|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_condition;

    protected function setUp()
    {
        $this->_condition = $this->getMockForAbstractClass(
            \Magento\Rule\Model\Condition\AbstractCondition::class,
            [],
            '',
            false,
            false,
            true,
            ['getInputType']
        );
    }

    public function testGetjointTables()
    {
        $this->_condition->setAttribute('category_ids');
        $this->assertEquals([], $this->_condition->getTablesToJoin());
        $this->_condition->setAttribute('gdsjkfghksldjfg');
        $this->assertEmpty($this->_condition->getTablesToJoin());
    }

    public function testGetMappedSqlField()
    {
        $this->_condition->setAttribute('category_ids');
        $this->assertEquals('category_ids', $this->_condition->getMappedSqlField());
    }

    public function validateAttributeDataProvider()
    {
        return [
            // value, operator, valueForValidate, expectedResult
            [1, '==', new \stdClass(), false],
            [new \stdClass(), '==', new \stdClass(), false],

            [1, '==', 1, true],
            [0, '==', 1, false],
            ['0', '==', 1, false],
            ['1', '==', 1, true],
            ['x', '==', 'x', true],
            ['x', '==', 0, false],

            [1, '!=', 1, false],
            [0, '!=', 1, true],
            ['0', '!=', 1, true],
            ['1', '!=', 1, false],
            ['x', '!=', 'x', false],
            ['x', '!=', 0, true],

            [1, '==', [1], true],
            [1, '!=', [1], false],
            [1, '==', [3, 1, 5], false],
            [1, '!=', [1, 5], true],

            [[1,2,3], '==', '1,2,3', false],
            [[1], '==', 1, false],

            // Note: validated value is on the right, so read expression in the array from right to left
            // e.g.: 1, <=, 0 actually is 0 <= 1.
            [1, '>', 1, false],
            [1, '<=', 1, true],
            [1, '<=', '1', true],
            [1, '<=', 0, true],
            [0, '>', [1], false],

            [1, '<', 1, false],
            [1, '>=', 1, true],
            [1, '>=', '1', true],
            [1, '>=', 0, false],
            [0, '<', [1], false],
        ];
    }

    /**
     * @param $existingValue
     * @param $operator
     * @param $valueForValidate
     * @param $expectedResult
     *
     * @dataProvider validateAttributeDataProvider
     */
    public function testValidateAttribute($existingValue, $operator, $valueForValidate, $expectedResult)
    {
        $this->_condition->setOperator($operator);
        $this->_condition->setData('value_parsed', $existingValue);
        $this->assertEquals(
            $expectedResult,
            $this->_condition->validateAttribute($valueForValidate),
            "Failed asserting that "
            . var_export($existingValue, true)
            . $operator
            . var_export($valueForValidate, true)
        );
    }

    /**
     * @param $existingValue
     * @param $operator
     * @param $valueForValidate
     * @param $expectedResult
     *
     * @dataProvider validateAttributeDataProvider
     */
    public function testValidate($existingValue, $operator, $valueForValidate, $expectedResult)
    {
        $objectMock = $this->getMock(
            \Magento\Framework\Model\AbstractModel::class,
            ['hasData', 'load', 'getId', 'getData'],
            [],
            '',
            false
        );
        $objectMock->expects($this->once())
            ->method('hasData')
            ->willReturn(false);
        $objectMock->expects($this->once())
            ->method('getId')
            ->willReturn(7);
        $objectMock->expects($this->once())
            ->method('load')
            ->with(7);
        $objectMock->expects($this->once())
            ->method('getData')
            ->willReturn($valueForValidate);

        $this->_condition->setOperator($operator);
        $this->_condition->setData('value_parsed', $existingValue);
        $this->assertEquals(
            $expectedResult,
            $this->_condition->validate($objectMock),
            "Failed asserting that "
            . var_export($existingValue, true)
            . $operator
            . var_export($valueForValidate, true)
        );
    }

    public function validateAttributeArrayInputTypeDataProvider()
    {
        return [
            // value, operator, valueForValidate, expectedResult, inputType
            [[1, 2, 3], '==', [2, 1, 3], true, 'multiselect'],
            [[1, 2], '==', [2, 3], true, 'multiselect'],
            [[1, 1, 3], '==', [2, 4], false, 'multiselect'],
            [[1, 2], '!=', [2, 3], false, 'multiselect'],
            [[1, 2], '!=', 1, false, 'multiselect'],

            [[1, 2, 3], '{}', '1', true, 'grid'],
            [[1, 2, 3], '{}', '8', false, 'grid'],
            [[1, 2, 3], '{}', 5, false, 'grid'],
            [[1, 2, 3], '{}', [2, 3, 4], true, 'grid'],
            [[1, 2, 3], '{}', [4], false, 'grid'],
            [[3], '{}', [], false, 'grid'],
            [1, '{}', 1, false, 'grid'],
            [1, '!{}', [1, 2, 3], false, 'grid'],
            [[1], '{}', null, false, 'grid'],
            [null, '{}', null, true, 'input'],
            [null, '!{}', null, false, 'input'],
            [null, '{}', [1], false, 'input'],

            [[1, 2, 3], '()', 1, true, 'select'],
            [[1, 2, 3], '!()', 1, false, 'select'],
            [[1], '()', 3, false, 'select'],
            [[1], '!()', 3, true, 'select'],
            [3, '()', 3, false, 'select'],
            [[3], '()', [3], true, 'select'],
            [3, '()', [3], false, 'select'],

        ];
    }

    /**
     * @param $existingValue
     * @param $operator
     * @param $valueForValidate
     * @param $expectedResult
     * @param $inputType
     *
     * @dataProvider validateAttributeArrayInputTypeDataProvider
     */
    public function testValidateArrayOperatorType(
        $existingValue,
        $operator,
        $valueForValidate,
        $expectedResult,
        $inputType
    ) {
        $this->_condition->setOperator($operator);
        $this->_condition->setData('value_parsed', $existingValue);
        $this->_condition->getDefaultOperatorInputByType();
        $this->_condition
            ->expects($this->any())
            ->method('getInputType')
            ->will($this->returnValue($inputType));

        $this->assertEquals(
            $expectedResult,
            $this->_condition->validateAttribute($valueForValidate),
            "Failed asserting that "
            . var_export($existingValue, true)
            . $operator
            . var_export($valueForValidate, true)
        );
    }

    public function testGetValueParsed()
    {
        $value = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $this->_condition->setValue(['1,2,3,4,5,6,7,8,9']);
        $this->_condition->setOperator('()');
        $this->assertEquals($value, $this->_condition->getValueParsed());
    }
}
