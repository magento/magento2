<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Select
     */
    protected $model;

    protected function setUp()
    {
        $timezoneMock = $this->getMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class, [], [], '', false);
        $localeResolverMock = $this->getMock(\Magento\Framework\Locale\ResolverInterface::class);

        $this->model = new \Magento\Eav\Model\Attribute\Data\Select($timezoneMock, $loggerMock, $localeResolverMock);
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Select::outputValue
     *
     * @param string $format
     * @param mixed $value
     * @param mixed $expectedResult
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValue($format, $value, $expectedResult)
    {
        $entityMock = $this->getMock(\Magento\Framework\Model\AbstractModel::class, [], [], '', false);
        $entityMock->expects($this->once())->method('getData')->will($this->returnValue($value));

        $sourceMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class,
            [],
            [],
            '',
            false
        );
        $sourceMock->expects($this->any())->method('getOptionText')->will($this->returnValue(123));

        $attributeMock = $this->getMock(\Magento\Eav\Model\Attribute::class, [], [], '', false);
        $attributeMock->expects($this->any())->method('getSource')->will($this->returnValue($sourceMock));

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
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON,
                'value' => 'value',
                'expectedResult' => 'value',
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => '',
                'expectedResult' => ''
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => 'value',
                'expectedResult' => '123'
            ],
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Select::validateValue
     *
     * @param mixed $value
     * @param mixed $originalValue
     * @param bool $isRequired
     * @param array $expectedResult
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $originalValue, $isRequired, $expectedResult)
    {
        $entityMock = $this->getMock(\Magento\Framework\Model\AbstractModel::class, [], [], '', false);
        $entityMock->expects($this->any())->method('getData')->will($this->returnValue($originalValue));

        $attributeMock = $this->getMock(\Magento\Eav\Model\Attribute::class, [], [], '', false);
        $attributeMock->expects($this->any())->method('getStoreLabel')->will($this->returnValue('Label'));
        $attributeMock->expects($this->any())->method('getIsRequired')->will($this->returnValue($isRequired));

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
                'originalValue' => 'value',
                'isRequired' => false,
                'expectedResult' => true,
            ],
            [
                'value' => false,
                'originalValue' => null,
                'isRequired' => true,
                'expectedResult' => ['"Label" is a required value.'],
            ],
            [
                'value' => false,
                'originalValue' => null,
                'isRequired' => false,
                'expectedResult' => true,
            ],
            [
                'value' => false,
                'originalValue' => '0',
                'isRequired' => true,
                'expectedResult' => true,
            ],
            [
                'value' => 'value',
                'originalValue' => '',
                'isRequired' => true,
                'expectedResult' => true,
            ]
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Select::compactValue
     */
    public function testCompactValue()
    {
        $entityMock = $this->getMock(\Magento\Framework\Model\AbstractModel::class, [], [], '', false);
        $entityMock->expects($this->once())->method('setData')->with('attrCode', 'value');

        $attributeMock = $this->getMock(\Magento\Eav\Model\Attribute::class, [], [], '', false);
        $attributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('attrCode'));

        $this->model->setAttribute($attributeMock);
        $this->model->setEntity($entityMock);
        $this->model->compactValue('value');
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Select::compactValue
     */
    public function testCompactValueWithFalseValue()
    {
        $entityMock = $this->getMock(\Magento\Framework\Model\AbstractModel::class, [], [], '', false);
        $entityMock->expects($this->never())->method('setData');

        $this->model->setEntity($entityMock);
        $this->model->compactValue(false);
    }
}
