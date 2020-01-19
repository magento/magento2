<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\IndexResolver;

/**
 * @SuppressWarnings(PHPMD)
 */
class IndexResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IndexResolver
     */
    private $resolver;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @var FieldTypeResolver
     */
    private $fieldTypeResolver;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->converter = $this->getMockBuilder(ConverterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMockForAbstractClass();
        $this->fieldTypeConverter = $this->getMockBuilder(FieldTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMockForAbstractClass();
        $this->fieldTypeResolver = $this->getMockBuilder(FieldTypeResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldType'])
            ->getMockForAbstractClass();
        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            IndexResolver::class,
            [
                'converter' => $this->converter,
                'fieldTypeConverter' => $this->fieldTypeConverter,
                'fieldTypeResolver' => $this->fieldTypeResolver,
            ]
        );
    }

    /**
     * @dataProvider getFieldIndexProvider
     * @param $isSearchable
     * @param $isAlwaysIndexable
     * @param $isComplexType
     * @param $isIntegerType
     * @param $isBooleanType
     * @param $isUserDefined
     * @param $isFloatType
     * @param $serviceFieldType
     * @param $expected
     * @return void
     */
    public function testGetFieldName(
        $isSearchable,
        $isAlwaysIndexable,
        $isComplexType,
        $isIntegerType,
        $isBooleanType,
        $isUserDefined,
        $isFloatType,
        $serviceFieldType,
        $expected
    ) {
        $this->converter->expects($this->any())
            ->method('convert')
            ->willReturn('something');
        $this->fieldTypeResolver->expects($this->any())
            ->method('getFieldType')
            ->willReturn($serviceFieldType);
        $this->fieldTypeConverter->expects($this->any())
            ->method('convert')
            ->willReturn('string');
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isSearchable',
                'isAlwaysIndexable',
                'isComplexType',
                'isIntegerType',
                'isBooleanType',
                'isUserDefined',
                'isFloatType',
            ])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isSearchable')
            ->willReturn($isSearchable);
        $attributeMock->expects($this->any())
            ->method('isAlwaysIndexable')
            ->willReturn($isAlwaysIndexable);
        $attributeMock->expects($this->any())
            ->method('isComplexType')
            ->willReturn($isComplexType);
        $attributeMock->expects($this->any())
            ->method('isIntegerType')
            ->willReturn($isIntegerType);
        $attributeMock->expects($this->any())
            ->method('isBooleanType')
            ->willReturn($isBooleanType);
        $attributeMock->expects($this->any())
            ->method('isUserDefined')
            ->willReturn($isUserDefined);
        $attributeMock->expects($this->any())
            ->method('isFloatType')
            ->willReturn($isFloatType);

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldIndex($attributeMock)
        );
    }

    /**
     * @return array
     */
    public function getFieldIndexProvider()
    {
        return [
            [true, true, true, true, true, true, true, 'string', null],
            [false, false, true, false, false, false, false, 'string', 'something'],
            [false, false, true, false, false, false, false, 'string', 'something'],
            [false, false, false, false, false, false, false, 'string', 'something'],
            [false, false, false, false, false, false, false, 'int', null],
            [false, false, true, true, false, true, false, 'string', 'something'],
            [false, false, true, false, true, true, false, 'string', 'something'],
            [false, false, true, false, true, false, false, 'string', null],
            [false, false, true, false, true, true, true, 'string', null],
        ];
    }
}
