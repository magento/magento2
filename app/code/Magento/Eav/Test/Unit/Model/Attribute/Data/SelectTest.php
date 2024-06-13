<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\Select;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SelectTest extends TestCase
{
    /**
     * @var Select
     */
    protected $model;

    protected function setUp(): void
    {
        $timezoneMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);

        $this->model = new Select($timezoneMock, $loggerMock, $localeResolverMock);
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
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())->method('getData')->willReturn($value);

        $sourceMock = $this->createMock(AbstractSource::class);
        $sourceMock->expects($this->any())->method('getOptionText')->willReturn(123);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getSource')->willReturn($sourceMock);

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->outputValue($format));
    }

    /**
     * @return array
     */
    public static function outputValueDataProvider()
    {
        return [
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_JSON,
                'value' => 'value',
                'expectedResult' => 'value',
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => '',
                'expectedResult' => ''
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
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
     * @param bool $skipRequiredValidation
     * @param array $expectedResult
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $originalValue, $isRequired, $skipRequiredValidation, $expectedResult)
    {
        $entityMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->addMethods(['getSkipRequiredValidation'])
            ->getMock();
        $entityMock->expects($this->any())->method('getData')->willReturn($originalValue);
        $entityMock->expects($this->any())
            ->method('getSkipRequiredValidation')
            ->willReturn($skipRequiredValidation);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getStoreLabel')->willReturn('Label');
        $attributeMock->expects($this->any())->method('getIsRequired')->willReturn($isRequired);

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->validateValue($value));
    }

    /**
     * @return array
     */
    public static function validateValueDataProvider()
    {
        return [
            [
                'value' => false,
                'originalValue' => 'value',
                'isRequired' => false,
                'skipRequiredValidation' => false,
                'expectedResult' => true,
            ],
            [
                'value' => false,
                'originalValue' => null,
                'isRequired' => true,
                'skipRequiredValidation' => false,
                'expectedResult' => ['"Label" is a required value.'],
            ],
            [
                'value' => false,
                'originalValue' => null,
                'isRequired' => false,
                'skipRequiredValidation' => true,
                'expectedResult' => true,
            ],
            [
                'value' => false,
                'originalValue' => '0',
                'isRequired' => true,
                'skipRequiredValidation' => true,
                'expectedResult' => true,
            ],
            [
                'value' => 'value',
                'originalValue' => '',
                'isRequired' => true,
                'skipRequiredValidation' => true,
                'expectedResult' => true,
            ]
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Select::compactValue
     */
    public function testCompactValue()
    {
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())->method('setData')->with('attrCode', 'value');

        $attributeMock = $this->createMock(Attribute::class);
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
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->never())->method('setData');

        $this->model->setEntity($entityMock);
        $this->model->compactValue(false);
    }
}
