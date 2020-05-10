<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\Boolean;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class BooleanTest extends TestCase
{
    /**
     * @var Boolean
     */
    protected $model;

    protected function setUp(): void
    {
        $timezoneMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);

        $this->model = new Boolean($timezoneMock, $loggerMock, $localeResolverMock);
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Boolean::_getOptionText
     *
     * @param string $format
     * @param mixed $value
     * @param mixed $expectedResult
     * @dataProvider getOptionTextDataProvider
     */
    public function testOutputValue($format, $value, $expectedResult)
    {
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())->method('getData')->willReturn($value);

        $attributeMock = $this->createMock(Attribute::class);

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->outputValue($format));
    }

    /**
     * @return array
     */
    public function getOptionTextDataProvider()
    {
        return [
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => '0',
                'expectedResult' => 'No',
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => '1',
                'expectedResult' => 'Yes'
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => '2',
                'expectedResult' => ''
            ],
        ];
    }
}
