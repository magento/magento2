<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver\DateTimeType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD)
 */
class DateTimeTypeTest extends TestCase
{
    /**
     * @var DateTimeType
     */
    private $resolver;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->fieldTypeConverter = $this->createPartialMock(FieldTypeConverterInterface::class, ['convert']);

        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            DateTimeType::class,
            [
                'fieldTypeConverter' => $this->fieldTypeConverter,
            ]
        );
    }

    /**
     * @param $isDateTimeType
     * @param $expected
     * @return void
     */
    #[DataProvider('getFieldTypeProvider')]
    public function testGetFieldType($isDateTimeType, $expected)
    {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isDateTimeType'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isDateTimeType')
            ->willReturn($isDateTimeType);
        $this->fieldTypeConverter->expects($this->any())
            ->method('convert')
            ->willReturn('something');

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldType($attributeMock)
        );
    }

    /**
     * @return array
     */
    public static function getFieldTypeProvider()
    {
        return [
            [true, 'something'],
            [false, ''],
        ];
    }
}
