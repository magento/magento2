<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\Multiselect;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class MultiselectTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Multiselect
     */
    protected $model;

    protected function setUp(): void
    {
        $timezoneMock = $this->createMock(TimezoneInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $localeResolverMock = $this->createMock(ResolverInterface::class);

        $this->model = new Multiselect(
            $timezoneMock,
            $loggerMock,
            $localeResolverMock
        );
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Multiselect::extractValue
     *
     * @param mixed $param
     * @param mixed $expectedResult
     */
    #[DataProvider('extractValueDataProvider')]
    public function testExtractValue($param, $expectedResult)
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $attributeMock = $this->createMock(Attribute::class);

        $requestMock->expects($this->once())->method('getParam')->willReturn($param);
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attributeCode');

        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->extractValue($requestMock));
    }

    /**
     * @return array
     */
    public static function extractValueDataProvider()
    {
        return [
            [
                'param' => 'param',
                'expectedResult' => ['param'],
            ],
            [
                'param' => false,
                'expectedResult' => false
            ],
            [
                'param' => ['value'],
                'expectedResult' => ['value']
            ]
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Multiselect::outputValue
     *
     * @param string $format
     * @param mixed $expectedResult
     */
    #[DataProvider('outputValueDataProvider')]
    public function testOutputValue($format, $expectedResult)
    {
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())->method('getData')->willReturn('value1,value2,');

        $sourceMock = $this->createMock(AbstractSource::class);
        $sourceMock->expects($this->any())->method('getOptionText')->willReturnArgument(0);

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
                'expectedResult' => 'value1, value2',
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_ONELINE,
                'expectedResult' => 'value1, value2'
            ]
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
     */
    #[DataProvider('validateValueDataProvider')]
    public function testValidateValue($value, $originalValue, $isRequired, $skipRequiredValidation, $expectedResult)
    {
        $entityMock = $this->createPartialMockWithReflection(
            AbstractModel::class,
            ['getSkipRequiredValidation', 'getData']
        );
        $entityMock->expects($this->any())->method('getData')->willReturn($originalValue);
        $entityMock->expects($this->any())
            ->method('getSkipRequiredValidation')
            ->willReturn($skipRequiredValidation);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getStoreLabel')->willReturn('Test');
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
                'skipRequiredValidation' => true,
                'expectedResult' => true,
            ],
            [
                'value' => false,
                'originalValue' => null,
                'isRequired' => true,
                'skipRequiredValidation' => false,
                'expectedResult' => ['"Test" is a required value.'],
            ]
        ];
    }
}
