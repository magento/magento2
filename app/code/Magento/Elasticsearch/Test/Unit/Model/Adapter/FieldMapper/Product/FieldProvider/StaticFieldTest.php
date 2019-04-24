<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
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

/**
 * @SuppressWarnings(PHPMD)
 */
class StaticFieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField
     */
    private $provider;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @var IndexTypeConverterInterface
     */
    private $indexTypeConverter;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var FieldIndexResolver
     */
    private $fieldIndexResolver;

    /**
     * @var FieldTypeResolver
     */
    private $fieldTypeResolver;

    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * Set up test environment
     *
     * @return void
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
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField::class,
            [
                'eavConfig' => $this->eavConfig,
                'fieldTypeConverter' => $this->fieldTypeConverter,
                'indexTypeConverter' => $this->indexTypeConverter,
                'attributeAdapterProvider' => $this->attributeAdapterProvider,
                'fieldIndexResolver' => $this->fieldIndexResolver,
                'fieldTypeResolver' => $this->fieldTypeResolver,
                'fieldNameResolver' => $this->fieldNameResolver,
            ]
        );
    }

    /**
     * @dataProvider attributeProvider
     * @param string $attributeCode
     * @param string $inputType
     * @param $indexType
     * @param $isComplexType
     * @param $complexType
     * @param $isSortable
     * @param $fieldName
     * @param $compositeFieldName
     * @param $sortFieldName
     * @param array $expected
     * @return void
     */
    public function testGetAllAttributesTypes(
        $attributeCode,
        $inputType,
        $indexType,
        $isComplexType,
        $complexType,
        $isSortable,
        $fieldName,
        $compositeFieldName,
        $sortFieldName,
        $expected
    ) {
        $this->fieldTypeResolver->expects($this->any())
            ->method('getFieldType')
            ->willReturn($inputType);
        $this->fieldIndexResolver->expects($this->any())
            ->method('getFieldIndex')
            ->willReturn($indexType);
        $this->indexTypeConverter->expects($this->any())
            ->method('convert')
            ->with($this->anything())
            ->will($this->returnCallback(
                function ($type) {
                    if ($type === 'no_index') {
                        return 'no';
                    } elseif ($type === 'no_analyze') {
                        return 'not_analyzed';
                    }
                }
            ));
        $this->fieldNameResolver->expects($this->any())
            ->method('getFieldName')
            ->with($this->anything())
            ->will($this->returnCallback(
                function ($attributeMock, $context) use ($fieldName, $compositeFieldName, $sortFieldName) {
                    if (empty($context)) {
                        return $fieldName;
                    } elseif ($context['type'] === 'sort') {
                        return $sortFieldName;
                    } elseif ($context['type'] === 'text') {
                        return $compositeFieldName;
                    }
                }
            ));

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
            ->setMethods(['isComplexType', 'getAttributeCode', 'isSortable'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isComplexType')
            ->willReturn($isComplexType);
        $attributeMock->expects($this->any())
            ->method('isSortable')
            ->willReturn($isSortable);
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
            ->will($this->returnCallback(
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
            ));

        $this->assertEquals(
            $expected,
            $this->provider->getFields(['storeId' => 1])
        );
    }

    /**
     * @return array
     */
    public function attributeProvider()
    {
        return [
            [
                'category_ids',
                'select',
                true,
                true,
                'text',
                false,
                'category_ids',
                'category_ids_value',
                '',
                [
                    'category_ids' => [
                        'type' => 'select',
                        'index' => true
                    ],
                    'category_ids_value' => [
                        'type' => 'string'
                    ],
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no'
                    ]
                ]
            ],
            [
                'attr_code',
                'text',
                'no',
                false,
                null,
                false,
                'attr_code',
                '',
                '',
                [
                    'attr_code' => [
                        'type' => 'text',
                        'index' => 'no'
                    ],
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no'
                    ]
                ],
            ],
            [
                'attr_code',
                'text',
                null,
                false,
                null,
                false,
                'attr_code',
                '',
                '',
                [
                    'attr_code' => [
                        'type' => 'text'
                    ],
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no'
                    ]
                ]
            ],
            [
                'attr_code',
                'text',
                null,
                false,
                null,
                true,
                'attr_code',
                '',
                'sort_attr_code',
                [
                    'attr_code' => [
                        'type' => 'text',
                        'fields' => [
                            'sort_attr_code' => [
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ]
                        ]
                    ],
                    'store_id' => [
                        'type' => 'string',
                        'index' => 'no'
                    ]
                ]
            ]
        ];
    }
}
