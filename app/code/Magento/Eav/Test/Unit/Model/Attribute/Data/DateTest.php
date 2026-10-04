<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\Date;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class DateTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Date
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $timezoneMock;

    protected function setUp(): void
    {
        $this->timezoneMock = $this->createMock(TimezoneInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $localeResolverMock = $this->createMock(ResolverInterface::class);

        $this->model = new Date(
            $this->timezoneMock,
            $loggerMock,
            $localeResolverMock
        );
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Date::outputValue
     *
     * @param string $format
     * @param mixed $value
     * @param mixed $expectedResult
     * @param int $callTimes
     */
    #[DataProvider('outputValueDataProvider')]
    public function testOutputValue($format, $value, $callTimes, $expectedResult)
    {
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())->method('getData')->willReturn($value);

        $attributeMock = $this->createPartialMockWithReflection(
            Attribute::class,
            ['getInputFilter', '__wakeup']
        );
        $attributeMock->expects($this->exactly($callTimes))->method('getInputFilter')->willReturn(false);

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
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => 'value',
                'callTimes' => 1,
                'expectedResult' => 'value',
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
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
     * @param bool $skipRequiredValidation
     * @param array $expectedResult
     */
    #[DataProvider('validateValueDataProvider')]
    public function testValidateValue(
        $value,
        $rules,
        $originalValue,
        $isRequired,
        $skipRequiredValidation,
        $expectedResult
    ) {
        $entityMock = $this->createPartialMockWithReflection(
            AbstractModel::class,
            ['getSkipRequiredValidation', 'getDataUsingMethod']
        );
        $entityMock->expects($this->any())->method('getDataUsingMethod')->willReturn($originalValue);
        $entityMock->expects($this->any())
            ->method('getSkipRequiredValidation')
            ->willReturn($skipRequiredValidation);
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getStoreLabel')->willReturn('Label');
        $attributeMock->expects($this->any())->method('getIsRequired')->willReturn($isRequired);
        $attributeMock->expects($this->any())->method('getValidateRules')->willReturn($rules);

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
                'rules' => [],
                'originalValue' => false,
                'isRequired' => true,
                'skipRequiredValidation' => false,
                'expectedResult' => ['"Label" is a required value.'],
            ],
            [
                'value' => 'value',
                'rules' => [],
                'originalValue' => 'value',
                'isRequired' => false,
                'skipRequiredValidation' => false,
                'expectedResult' => true,
            ],
            [
                'value' => false,
                'rules' => [],
                'originalValue' => '',
                'isRequired' => true,
                'skipRequiredValidation' => true,
                'expectedResult' => true,
            ],
            [
                'value' => null,
                'rules' => [],
                'originalValue' => '',
                'isRequired' => false,
                'skipRequiredValidation' => false,
                'expectedResult' => true,
            ],
            [
                'value' => '2000-01-01',
                'rules' => ['date_range_min' => strtotime('2001-01-01'),'date_range_max' => strtotime('2002-01-01')],
                'originalValue' => '',
                'isRequired' => false,
                'skipRequiredValidation' => false,
                'expectedResult' => ['Please enter a valid date between 01/01/2001 and 01/01/2002 at Label.'],
            ],
            [
                'value' => '2000-01-01',
                'rules' => ['date_range_min' => strtotime('2001-01-01')],
                'originalValue' => '',
                'isRequired' => false,
                'skipRequiredValidation' => false,
                'expectedResult' => ['Please enter a valid date equal to or greater than 01/01/2001 at Label.'],
            ],
            [
                'value' => '2010-01-01',
                'rules' => ['date_range_max' => strtotime('2001-01-01')],
                'originalValue' => '',
                'isRequired' => false,
                'skipRequiredValidation' => false,
                'expectedResult' => ['Please enter a valid date less than or equal to 01/01/2001 at Label.'],
            ],
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Date::compactValue
     *
     * @param string $value
     * @param string $expectedResult
     */
    #[DataProvider('compactValueDataProvider')]
    public function testCompactValue($value, $expectedResult)
    {
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())->method('setDataUsingMethod')->with('attrCode', $expectedResult);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('attrCode');

        $this->model->setAttribute($attributeMock);
        $this->model->setEntity($entityMock);
        $this->model->compactValue($value);
    }

    /**
     * @return array
     */
    public static function compactValueDataProvider()
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
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->never())->method('setDataUsingMethod');

        $this->model->setEntity($entityMock);
        $this->model->compactValue(false);
    }
}
