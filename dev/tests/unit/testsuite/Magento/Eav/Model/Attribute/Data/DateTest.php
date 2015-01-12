<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Date
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneMock;

    protected function setUp()
    {
        $this->timezoneMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $loggerMock = $this->getMock('\Psr\Log\LoggerInterface', [], [], '', false);
        $localeResolverMock = $this->getMock('\Magento\Framework\Locale\ResolverInterface');

        $this->model = new Date($this->timezoneMock, $loggerMock, $localeResolverMock);
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Date::outputValue
     *
     * @param string $format
     * @param mixed $value
     * @param mixed $expectedResult
     * @param int $callTimes
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValue($format, $value, $callTimes, $expectedResult)
    {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->once())->method('getData')->will($this->returnValue($value));

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', ['getInputFilter', '__wakeup'], [], '', false);
        $attributeMock->expects($this->exactly($callTimes))->method('getInputFilter')->will($this->returnValue(false));

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->outputValue($format));
    }

    /**
     * @return array
     */
    public function outputValueDataProvider()
    {
        return [
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => 'value',
                'callTimes' => 1,
                'expectedResult' => 'value',
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => false,
                'callTimes' => 0,
                'expectedResult' => false
            ],
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Date::validateValue
     *
     * @param mixed $value
     * @param array $rules
     * @param mixed $originalValue
     * @param bool $isRequired
     * @param array $expectedResult
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $rules, $originalValue, $isRequired, $expectedResult)
    {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->any())->method('getDataUsingMethod')->will($this->returnValue($originalValue));

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);
        $attributeMock->expects($this->any())->method('getStoreLabel')->will($this->returnValue('Label'));
        $attributeMock->expects($this->any())->method('getIsRequired')->will($this->returnValue($isRequired));
        $attributeMock->expects($this->any())->method('getValidateRules')->will($this->returnValue($rules));

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->validateValue($value));
    }

    /**
     * @return array
     */
    public function validateValueDataProvider()
    {
        return [
            [
                'value' => false,
                'rules' => [],
                'originalValue' => false,
                'isRequired' => true,
                'expectedResult' => ['"Label" is a required value.'],
            ],
            [
                'value' => 'value',
                'rules' => [],
                'originalValue' => 'value',
                'isRequired' => false,
                'expectedResult' => true,
            ],
            [
                'value' => null,
                'rules' => [],
                'originalValue' => '',
                'isRequired' => false,
                'expectedResult' => true,
            ],
            [
                'value' => '2000-01-01',
                'rules' => ['date_range_min' => strtotime('2001-01-01'),'date_range_max' => strtotime('2002-01-01')],
                'originalValue' => '',
                'isRequired' => false,
                'expectedResult' => ['Please enter a valid date between 01/01/2001 and 01/01/2002 at Label.'],
            ],
            [
                'value' => '2000-01-01',
                'rules' => ['date_range_min' => strtotime('2001-01-01')],
                'originalValue' => '',
                'isRequired' => false,
                'expectedResult' => ['Please enter a valid date equal to or greater than 01/01/2001 at Label.'],
            ],
            [
                'value' => '2010-01-01',
                'rules' => ['date_range_max' => strtotime('2001-01-01')],
                'originalValue' => '',
                'isRequired' => false,
                'expectedResult' => ['Please enter a valid date less than or equal to 01/01/2001 at Label.'],
            ],
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Date::compactValue
     *
     * @param string $value
     * @param string $expectedResult
     * @dataProvider compactValueDataProvider
     */
    public function testCompactValue($value, $expectedResult)
    {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->once())->method('setDataUsingMethod')->with('attrCode', $expectedResult);

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);
        $attributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('attrCode'));

        $this->model->setAttribute($attributeMock);
        $this->model->setEntity($entityMock);
        $this->model->compactValue($value);
    }

    /**
     * @return array
     */
    public function compactValueDataProvider()
    {
        return [
            ['value' => 'value', 'expectedResult' => 'value'],
            ['value' => '',  'expectedResult' => null]
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Date::compactValue
     */
    public function testCompactValueWithFalseValue()
    {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->never())->method('setDataUsingMethod');

        $this->model->setEntity($entityMock);
        $this->model->compactValue(false);
    }
}
