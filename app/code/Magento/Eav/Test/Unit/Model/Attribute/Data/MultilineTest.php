<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\Multiline;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MultilineTest extends TestCase
{
    /**
     * @var Multiline
     */
    protected $model;

    /**
     * @var MockObject|StringUtils
     */
    protected $stringMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        /** @var TimezoneInterface $timezoneMock */
        $timezoneMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        /** @var LoggerInterface $loggerMock */
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        /** @var ResolverInterface $localeResolverMock */
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->stringMock = $this->createMock(StringUtils::class);

        $this->model = new Multiline(
            $timezoneMock,
            $loggerMock,
            $localeResolverMock,
            $this->stringMock
        );
    }

    /**
     * @covers       \Magento\Eav\Model\Attribute\Data\Multiline::extractValue
     *
     * @param mixed $param
     * @param mixed $expectedResult
     * @dataProvider extractValueDataProvider
     */
    public function testExtractValue($param, $expectedResult)
    {
        /** @var MockObject|RequestInterface $requestMock */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        /** @var MockObject|Attribute $attributeMock */
        $attributeMock = $this->createMock(Attribute::class);

        $requestMock->expects($this->once())->method('getParam')->willReturn($param);
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('attributeCode');

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
                'expectedResult' => false,
            ],
            [
                'param' => ['param'],
                'expectedResult' => ['param']
            ],
        ];
    }

    /**
     * @covers       \Magento\Eav\Model\Attribute\Data\Multiline::outputValue
     *
     * @param string $format
     * @param mixed $expectedResult
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValue($format, $expectedResult)
    {
        /** @var MockObject|AbstractModel $entityMock */
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())
            ->method('getData')
            ->willReturn("value1\nvalue2");

        /** @var MockObject|Attribute $attributeMock */
        $attributeMock = $this->createMock(Attribute::class);
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
                'format' => AttributeDataFactory::OUTPUT_FORMAT_ARRAY,
                'expectedResult' => ['value1', 'value2'],
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_HTML,
                'expectedResult' => 'value1<br />value2'
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_ONELINE,
                'expectedResult' => 'value1 value2'
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'expectedResult' => "value1\nvalue2"
            ]
        ];
    }

    /**
     * @covers       \Magento\Eav\Model\Attribute\Data\Multiline::validateValue
     * @covers       \Magento\Eav\Model\Attribute\Data\Text::validateValue
     *
     * @param mixed $value
     * @param bool $isAttributeRequired
     * @param array $rules
     * @param array $expectedResult
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $isAttributeRequired, $rules, $expectedResult)
    {
        /** @var MockObject|AbstractModel $entityMock */
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturn("value1\nvalue2");

        $entityTypeMock = $this->createMock(Type::class);

        /** @var MockObject|Attribute $attributeMock */
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getMultilineCount')->willReturn(2);
        $attributeMock->expects($this->any())->method('getValidateRules')->willReturn($rules);
        $attributeMock->expects($this->any())
            ->method('getStoreLabel')
            ->willReturn('Label');

        $attributeMock->expects($this->any())
            ->method('getIsRequired')
            ->willReturn($isAttributeRequired);

        $attributeMock->expects($this->any())
            ->method('getEntityType')
            ->willReturn($entityTypeMock);

        $this->stringMock->expects($this->any())->method('strlen')->willReturn(5);

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
                'isAttributeRequired' => false,
                'rules' => [],
                'expectedResult' => true,
            ],
            [
                'value' => 'value',
                'isAttributeRequired' => false,
                'rules' => [],
                'expectedResult' => true,
            ],
            [
                'value' => ['value1', 'value2'],
                'isAttributeRequired' => false,
                'rules' => [],
                'expectedResult' => true,
            ],
            [
                'value' => 'value',
                'isAttributeRequired' => false,
                'rules' => ['input_validation' => 'other', 'max_text_length' => 3],
                'expectedResult' => ['"Label" length must be equal or less than 3 characters.'],
            ],
            [
                'value' => 'value',
                'isAttributeRequired' => false,
                'rules' => ['input_validation' => 'other', 'min_text_length' => 10],
                'expectedResult' => ['"Label" length must be equal or greater than 10 characters.'],
            ],
            [
                'value' => "value1\nvalue2\nvalue3",
                'isAttributeRequired' => false,
                'rules' => [],
                'expectedResult' => ['"Label" cannot contain more than 2 lines.'],
            ],
            [
                'value' => ['value1', 'value2', 'value3'],
                'isAttributeRequired' => false,
                'rules' => [],
                'expectedResult' => ['"Label" cannot contain more than 2 lines.'],
            ],
            [
                'value' => [],
                'isAttributeRequired' => true,
                'rules' => [],
                'expectedResult' => ['"Label" is a required value.'],
            ],
            [
                'value' => '',
                'isAttributeRequired' => true,
                'rules' => [],
                'expectedResult' => ['"Label" is a required value.'],
            ],
        ];
    }
}
