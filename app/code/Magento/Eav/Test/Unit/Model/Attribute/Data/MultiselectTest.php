<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Psr\Log\LoggerInterface;

class MultiselectTest extends TestCase
{
    /**
     * @var Multiselect
     */
    protected $model;

    protected function setUp(): void
    {
        $timezoneMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);

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
     * @dataProvider extractValueDataProvider
     */
    public function testExtractValue($param, $expectedResult)
    {
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $attributeMock = $this->createMock(Attribute::class);

        $requestMock->expects($this->once())->method('getParam')->willReturn($param);
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attributeCode');

        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->extractValue($requestMock));
    }

    /**
     * @return array
     */
    public function extractValueDataProvider()
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
     * @dataProvider outputValueDataProvider
     */
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
    public function outputValueDataProvider()
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
}
