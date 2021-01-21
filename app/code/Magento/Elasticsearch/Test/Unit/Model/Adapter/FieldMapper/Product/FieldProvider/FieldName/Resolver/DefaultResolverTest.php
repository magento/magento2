<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\DefaultResolver;

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
    protected function setUp(): void
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
     * @param $isSortable
     * @param $context
     * @param $expected
     * @return void
     */
    public function testGetFieldName(
        $fieldType,
        $attributeCode,
        $frontendInput,
        $isSortable,
        $context,
        $expected
    ) {
        $this->fieldTypeConverter->expects($this->any())
            ->method('convert')
            ->willReturn('string');
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontendInput', 'isSortable'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $attributeMock->expects($this->any())
            ->method('isSortable')
            ->willReturn($isSortable);
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
            ['', 'code', '', false, [], 'code'],
            ['', 'code', '', false, ['type' => 'default'], 'code'],
            ['string', '*', '', false, ['type' => 'default'], '_all'],
            ['', 'code', '', false, ['type' => 'default'], 'code'],
            ['', 'code', 'select', false, ['type' => 'default'], 'code'],
            ['', 'code', 'boolean', false, ['type' => 'default'], 'code'],
            ['', 'code', '', true, ['type' => 'sort'], 'sort_code'],
        ];
    }
}
