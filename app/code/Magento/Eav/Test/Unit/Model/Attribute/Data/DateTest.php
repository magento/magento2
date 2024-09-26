<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Psr\Log\LoggerInterface;

class DateTest extends TestCase
{
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
        $this->timezoneMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);

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
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValue($format, $value, $callTimes, $expectedResult)
    {
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())->method('getData')->willReturn($value);

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->addMethods(['getInputFilter'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
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
     * @param array $expectedResult
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $rules, $originalValue, $isRequired, $expectedResult)
    {
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->any())->method('getDataUsingMethod')->willReturn($originalValue);

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
