<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver\IntegerType;

/**
 * @SuppressWarnings(PHPMD)
 */
class IntegerTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IntegerType
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
    protected function setUp()
    {
        $this->fieldTypeConverter = $this->getMockBuilder(FieldTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            IntegerType::class,
            [
                'fieldTypeConverter' => $this->fieldTypeConverter,
            ]
        );
    }

    /**
     * @dataProvider getFieldTypeProvider
     * @param $attributeCode
     * @param $isIntegerType
     * @param $isBooleanType
     * @param $isUserDefined
     * @param $expected
     * @return void
     */
    public function testGetFieldType($attributeCode, $isIntegerType, $isBooleanType, $isUserDefined, $expected)
    {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'isIntegerType', 'isBooleanType', 'isUserDefined'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects($this->any())
            ->method('isIntegerType')
            ->willReturn($isIntegerType);
        $attributeMock->expects($this->any())
            ->method('isBooleanType')
            ->willReturn($isBooleanType);
        $attributeMock->expects($this->any())
            ->method('isUserDefined')
            ->willReturn($isUserDefined);
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
    public function getFieldTypeProvider()
    {
        return [
            ['category_ids', true, true, true, null],
            ['category_ids', false, false, false, null],
            ['type', true, false, false, 'something'],
            ['type', false, true, false, 'something'],
            ['type', true, true, true, ''],
            ['type', false, false, true, ''],
        ];
    }
}
