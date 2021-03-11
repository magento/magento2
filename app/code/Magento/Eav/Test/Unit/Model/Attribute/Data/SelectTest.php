<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

class SelectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Select
     */
    protected $model;

    protected function setUp(): void
    {
        $timezoneMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $localeResolverMock = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);

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
        $entityMock = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
        $entityMock->expects($this->once())->method('getData')->willReturn($value);

        $sourceMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class);
        $sourceMock->expects($this->any())->method('getOptionText')->willReturn(123);

        $attributeMock = $this->createMock(\Magento\Eav\Model\Attribute::class);
        $attributeMock->expects($this->any())->method('getSource')->willReturn($sourceMock);

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
        $entityMock = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
        $entityMock->expects($this->any())->method('getData')->willReturn($originalValue);

        $attributeMock = $this->createMock(\Magento\Eav\Model\Attribute::class);
        $attributeMock->expects($this->any())->method('getStoreLabel')->willReturn('Label');
        $attributeMock->expects($this->any())->method('getIsRequired')->willReturn($isRequired);

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
        $entityMock = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
        $entityMock->expects($this->once())->method('setData')->with('attrCode', 'value');

        $attributeMock = $this->createMock(\Magento\Eav\Model\Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('attrCode');

        $this->model->setAttribute($attributeMock);
        $this->model->setEntity($entityMock);
        $this->model->compactValue('value');
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Select::compactValue
     */
    public function testCompactValueWithFalseValue()
    {
        $entityMock = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
        $entityMock->expects($this->never())->method('setData');

        $this->model->setEntity($entityMock);
        $this->model->compactValue(false);
    }
}
