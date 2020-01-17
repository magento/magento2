<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ResolverInterface
    as FieldIndexResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField class.
 */
class StaticFieldTest extends TestCase
{
    /**
     * @var StaticField
     */
    private $provider;

    /**
     * @var Config|MockObject
     */
    private $eavConfig;

    /**
     * @var FieldTypeConverterInterface|MockObject
     */
    private $fieldTypeConverter;

    /**
     * @var IndexTypeConverterInterface|MockObject
     */
    private $indexTypeConverter;

    /**
     * @var AttributeProvider|MockObject
     */
    private $attributeAdapterProvider;

    /**
     * @var FieldIndexResolver|MockObject
     */
    private $fieldIndexResolver;

    /**
     * @var FieldTypeResolver|MockObject
     */
    private $fieldTypeResolver;

    /**
     * @var FieldNameResolver|MockObject
     */
    private $fieldNameResolver;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityAttributes'])
            ->getMock();
        $this->fieldTypeConverter = $this->getMockBuilder(FieldTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->indexTypeConverter = $this->getMockBuilder(IndexTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributeAdapterProvider = $this->getMockBuilder(AttributeProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByAttributeCode'])
            ->getMock();
        $this->fieldTypeResolver = $this->getMockBuilder(FieldTypeResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldType'])
            ->getMock();
        $this->fieldIndexResolver = $this->getMockBuilder(FieldIndexResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldIndex'])
            ->getMock();
        $this->fieldNameResolver = $this->getMockBuilder(FieldNameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldName'])
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);

        $this->provider = $objectManager->getObject(
            StaticField::class,
            [
                'eavConfig' => $this->eavConfig,
                'fieldTypeConverter' => $this->fieldTypeConverter,
                'indexTypeConverter' => $this->indexTypeConverter,
                'attributeAdapterProvider' => $this->attributeAdapterProvider,
                'fieldIndexResolver' => $this->fieldIndexResolver,
                'fieldTypeResolver' => $this->fieldTypeResolver,
                'fieldNameResolver' => $this->fieldNameResolver,
                'excludedAttributes' => ['price'],
            ]
        );
    }

    /**
     * @param string $attributeCode
     * @param string $inputType
     * @param string|bool $indexType
     * @param bool $isComplexType
     * @param string $complexType
     * @param bool $isSortable
     * @param bool $isTextType
     * @param string $fieldName
     * @param string $compositeFieldName
     * @param string $sortFieldName
     * @param array $expected
     * @return void
     * @dataProvider attributeProvider
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testGetAllAttributesTypes(
        string $attributeCode,
        string $inputType,
        $indexType,
        bool $isComplexType,
        string $complexType,
        bool $isSortable,
        bool $isTextType,
        string $fieldName,
        string $compositeFieldName,
        string $sortFieldName,
        array $expected
    ): void {
        $this->fieldTypeResolver->expects($this->any())
            ->method('getFieldType')
            ->willReturn($inputType);
        $this->fieldIndexResolver->expects($this->any())
            ->method('getFieldIndex')
            ->willReturn($indexType);
        $this->indexTypeConverter->expects($this->any())
            ->method('convert')
            ->with($this->anything())
            ->willReturnCallback(
                function ($type) {
                    if ($type === 'no_index') {
                        return 'no';
                    } elseif ($type === 'no_analyze') {
                        return 'not_analyzed';
                    }
                }
            );
        $this->fieldNameResolver->expects($this->any())
            ->method('getFieldName')
            ->with($this->anything())
            ->willReturnCallback(
                function ($attributeMock, $context) use ($fieldName, $compositeFieldName, $sortFieldName) {
                    if (empty($context)) {
                        return $fieldName;
                    } elseif ($context['type'] === 'sort') {
                        return $sortFieldName;
                    } elseif ($context['type'] === 'text') {
                        return $compositeFieldName;
                    }
                }
            );

        $productAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productAttributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->eavConfig->expects($this->any())->method('getEntityAttributes')
            ->willReturn([$productAttributeMock]);

        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['isComplexType', 'getAttributeCode', 'isSortable', 'isTextType'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isComplexType')
            ->willReturn($isComplexType);
        $attributeMock->expects($this->any())
            ->method('isSortable')
            ->willReturn($isSortable);
        $attributeMock->expects($this->any())
            ->method('isTextType')
            ->willReturn($isTextType);
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attributeAdapterProvider->expects($this->any())
            ->method('getByAttributeCode')
            ->with($this->anything())
            ->willReturn($attributeMock);
        $this->fieldTypeConverter->expects($this->any())
            ->method('convert')
            ->with($this->anything())
            ->willReturnCallback(
                function ($type) use ($complexType) {
                    static $callCount = [];
                    $callCount[$type] = !isset($callCount[$type]) ? 1 : ++$callCount[$type];

                    if ($type === 'string') {
                        return 'string';
                    } elseif ($type === 'float') {
                        return 'float';
                    } elseif ($type === 'keyword') {
                        return 'string';
                    } else {
                        return $complexType;
                    }
                }
            );

        $this->assertEquals(
            $expected,
            $this->provider->getFields(['storeId' => 1])
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function attributeProvider(): array
    {
        return [
            [
                'category_ids',
                'select',
                true,
                true,
                'text',
                false,
                true,
                'category_ids',
                'category_ids_value',
                '',
                [
                    'category_ids' => [
                        'type' => 'select',
                        'index' => true,
                        'fields' => [
                            'keyword' => [
                                'type' => 'string',
                                'index' => 'not_analyzed',
                            ],
                        ],
                    ],
                    'category_ids_value' => [
                        'type' => 'string',
                    ],
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no',
                    ],
                ],
            ],
            [
                'attr_code',
                'text',
                'no',
                false,
                'text',
                false,
                true,
                'attr_code',
                '',
                '',
                [
                    'attr_code' => [
                        'type' => 'text',
                        'index' => 'no',
                        'fields' => [
                            'keyword' => [
                                'type' => 'string',
                                'index' => 'not_analyzed',
                            ],
                        ],
                    ],
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no',
                    ],
                ],
            ],
            [
                'attr_code',
                'text',
                false,
                false,
                'text',
                false,
                false,
                'attr_code',
                '',
                '',
                [
                    'attr_code' => [
                        'type' => 'text',
                        'index' => false,
                    ],
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no',
                    ],
                ],
            ],
            [
                'attr_code',
                'text',
                false,
                false,
                'text',
                true,
                false,
                'attr_code',
                '',
                'sort_attr_code',
                [
                    'attr_code' => [
                        'type' => 'text',
                        'index' => false,
                        'fields' => [
                            'sort_attr_code' => [
                                'type' => 'string',
                                'index' => 'not_analyzed',
                            ],
                        ],
                    ],
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no',
                    ],
                ],
            ],
            [
                'price',
                'text',
                false,
                false,
                'text',
                false,
                false,
                'price',
                '',
                '',
                [
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no',
                    ],
                ],
            ],
        ];
    }
}
