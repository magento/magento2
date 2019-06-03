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

/**
 * @SuppressWarnings(PHPMD)
 */
class FloatTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver\FloatType
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
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver\FloatType::class,
            [
                'fieldTypeConverter' => $this->fieldTypeConverter,
            ]
        );
    }

    /**
     * @dataProvider getFieldTypeProvider
     * @param $isFloatType
     * @param $expected
     * @return void
     */
    public function testGetFieldType($isFloatType, $expected)
    {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFloatType'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isFloatType')
            ->willReturn($isFloatType);
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
            [true, 'something'],
            [false, ''],
        ];
    }
}
