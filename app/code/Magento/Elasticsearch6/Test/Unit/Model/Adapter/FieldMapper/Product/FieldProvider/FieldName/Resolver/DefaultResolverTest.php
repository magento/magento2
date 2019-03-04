<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch6\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\DefaultResolver;

/**
 * @SuppressWarnings(PHPMD)
 */
class DefaultResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DefaultResolver
     */
    private $resolver;

    /**
     * @var FieldTypeResolver
     */
    private $fieldTypeResolver;

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
        $objectManager = new ObjectManagerHelper($this);
        $this->fieldTypeResolver = $this->getMockBuilder(FieldTypeResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldType'])
            ->getMockForAbstractClass();
        $this->fieldTypeConverter = $this->getMockBuilder(FieldTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMockForAbstractClass();

        $this->resolver = $objectManager->getObject(
            DefaultResolver::class,
            [
                'fieldTypeResolver' => $this->fieldTypeResolver,
                'fieldTypeConverter' => $this->fieldTypeConverter
            ]
        );
    }

    /**
     * @dataProvider getFieldNameProvider
     * @param $fieldType
     * @param $attributeCode
     * @param $frontendInput
     * @param $context
     * @param $expected
     * @return void
     */
    public function testGetFieldName(
        $fieldType,
        $attributeCode,
        $frontendInput,
        $context,
        $expected
    ) {
        $this->fieldTypeConverter->expects($this->any())
            ->method('convert')
            ->willReturn('string');
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontendInput'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $this->fieldTypeResolver->expects($this->any())
            ->method('getFieldType')
            ->willReturn($fieldType);

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldName($attributeMock, $context)
        );
    }

    /**
     * @return array
     */
    public function getFieldNameProvider()
    {
        return [
            ['', 'code', '', [], 'code'],
            ['', 'code', '', ['type' => 'default'], 'code'],
            ['string', '*', '', ['type' => 'default'], '_search'],
            ['', 'code', '', ['type' => 'default'], 'code'],
            ['', 'code', 'select', ['type' => 'default'], 'code'],
            ['', 'code', 'boolean', ['type' => 'default'], 'code'],
            ['', 'code', '', ['type' => 'type'], 'sort_code'],
        ];
    }
}
