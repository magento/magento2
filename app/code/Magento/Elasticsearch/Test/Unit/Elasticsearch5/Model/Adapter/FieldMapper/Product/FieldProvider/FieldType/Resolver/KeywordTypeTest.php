<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace
Magento\Elasticsearch\Test\Unit\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver\KeywordType;

/**
 * @SuppressWarnings(PHPMD)
 */
class KeywordTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var KeywordType
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
            KeywordType::class,
            [
                'fieldTypeConverter' => $this->fieldTypeConverter,
            ]
        );
    }

    /**
     * @dataProvider getFieldTypeProvider
     * @param bool $isComplexType
     * @param bool $isSearchable
     * @param bool $isAlwaysIndexable
     * @param bool $isFilterable
     * @param bool $isBoolean
     * @param string $expected
     * @return void
     */
    public function testGetFieldType(
        bool $isComplexType,
        bool $isSearchable,
        bool $isAlwaysIndexable,
        bool $isFilterable,
        bool $isBoolean,
        string $expected
    ): void {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['isComplexType', 'isSearchable', 'isAlwaysIndexable', 'isFilterable', 'isBooleanType'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isComplexType')
            ->willReturn($isComplexType);
        $attributeMock->expects($this->any())
            ->method('isSearchable')
            ->willReturn($isSearchable);
        $attributeMock->expects($this->any())
            ->method('isAlwaysIndexable')
            ->willReturn($isAlwaysIndexable);
        $attributeMock->expects($this->any())
            ->method('isFilterable')
            ->willReturn($isFilterable);
        $attributeMock->expects($this->any())
            ->method('isBooleanType')
            ->willReturn($isBoolean);
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
            [true, true, true, true, false, 'something'],
            [true, false, false, false, false, 'something'],
            [true, false, false, true, false, 'something'],
            [false, false, false, true, false, 'something'],
            [false, false, false, false, false, ''],
            [false, false, true, false, false, ''],
            [false, true, false, false, false, ''],
            [true, true, true, true, true, ''],
        ];
    }
}
