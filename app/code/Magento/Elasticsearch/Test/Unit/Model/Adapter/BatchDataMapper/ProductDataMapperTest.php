<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldType\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductDataMapperTest extends TestCase
{
    /**
     * @var ProductDataMapper
     */
    private $model;

    /**
     * @var Builder|MockObject
     */
    private $builderMock;

    /**
     * @var Attribute|MockObject
     */
    private $attribute;

    /**
     * @var FieldMapperInterface|MockObject
     */
    private $fieldMapperMock;

    /**
     * @var Date|MockObject
     */
    private $dateFieldTypeMock;

    /**
     * @var AdditionalFieldsProviderInterface|MockObject
     */
    private $additionalFieldsProvider;

    /**
     * @var DataProvider|MockObject
     */
    private $dataProvider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->builderMock = $this->createTestProxy(Builder::class);
        $this->fieldMapperMock = $this->getMockForAbstractClass(FieldMapperInterface::class);
        $this->dataProvider = $this->createMock(DataProvider::class);
        $this->attribute = $this->createMock(Attribute::class);
        $this->additionalFieldsProvider = $this->getMockForAbstractClass(AdditionalFieldsProviderInterface::class);
        $this->dateFieldTypeMock = $this->createMock(Date::class);
        $filterableAttributeTypes = [
            'boolean' => 'boolean',
            'multiselect' => 'multiselect',
            'select' => 'select',
        ];

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            ProductDataMapper::class,
            [
                'builder' => $this->builderMock,
                'fieldMapper' => $this->fieldMapperMock,
                'dateFieldType' => $this->dateFieldTypeMock,
                'dataProvider' => $this->dataProvider,
                'additionalFieldsProvider' => $this->additionalFieldsProvider,
                'filterableAttributeTypes' => $filterableAttributeTypes,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetMapAdditionalFieldsOnly()
    {
        $storeId = 1;
        $productId = 42;
        $additionalFields = ['some data'];
        $this->builderMock->expects($this->once())
            ->method('addField')
            ->with('store_id', $storeId);

        $this->builderMock->expects($this->any())
            ->method('addFields')
            ->withConsecutive([$additionalFields])
            ->willReturnSelf();
        $this->builderMock->expects($this->any())
            ->method('build')
            ->willReturn([]);
        $this->additionalFieldsProvider->expects($this->once())
            ->method('getFields')
            ->with([$productId], $storeId)
            ->willReturn([$productId => $additionalFields]);

        $documents = $this->model->map([$productId => []], $storeId, []);
        $this->assertEquals([$productId], array_keys($documents));
    }

    /**
     * @return void
     */
    public function testGetMapEmptyData()
    {
        $storeId = 1;

        $this->builderMock->expects($this->never())->method('addField');
        $this->builderMock->expects($this->never())->method('build');
        $this->additionalFieldsProvider->expects($this->once())
            ->method('getFields')
            ->with([], $storeId)
            ->willReturn([]);

        $documents = $this->model->map([], $storeId, []);
        $this->assertEquals([], $documents);
    }

    /**
     * @param int $productId
     * @param array $attributeData
     * @param array|string $attributeValue
     * @param array $returnAttributeData
     * @dataProvider mapProvider
     */
    public function testGetMap(int $productId, array $attributeData, $attributeValue, array $returnAttributeData)
    {
        $storeId = 1;
        $attributeId = 5;
        $context = [];

        $this->dataProvider->method('getSearchableAttribute')
            ->with($attributeId)
            ->willReturn($this->getAttribute($attributeData));
        $this->fieldMapperMock->method('getFieldName')
            ->willReturnArgument(0);
        $this->dateFieldTypeMock->method('formatDate')
            ->willReturnArgument(1);
        $this->additionalFieldsProvider->expects($this->once())
            ->method('getFields')
            ->willReturn([]);

        $documentData = [
            $productId => [$attributeId => $attributeValue],
        ];
        $documents = $this->model->map($documentData, $storeId, $context);
        $returnAttributeData = ['store_id' => $storeId] + $returnAttributeData;
        $this->assertSame($returnAttributeData, $documents[$productId]);
    }

    /**
     * @return void
     */
    public function testGetMapWithOptions()
    {
        $storeId = 1;
        $productId = 10;
        $context = [];
        $attributeValue = ['o1', 'o2'];
        $returnAttributeData = [
            'store_id' => $storeId,
            'options' => $attributeValue,
        ];

        $this->dataProvider->expects($this->never())
            ->method('getSearchableAttribute');
        $this->fieldMapperMock->method('getFieldName')
            ->willReturnArgument(0);
        $this->additionalFieldsProvider->expects($this->once())
            ->method('getFields')
            ->willReturn([]);

        $documentData = [
            $productId => ['options' => $attributeValue],
        ];
        $documents = $this->model->map($documentData, $storeId, $context);
        $this->assertEquals($returnAttributeData, $documents[$productId]);
    }

    /**
     * Return attribute mock
     *
     * @param array attributeData
     * @return MockObject
     */
    private function getAttribute(array $attributeData): MockObject
    {
        $attributeMock = $this->createPartialMock(
            Attribute::class,
            [
                'getSource',
                'getOptions',
            ]
        );

        $sourceMock = $this->getMockForAbstractClass(SourceInterface::class);
        $attributeMock->method('getSource')->willReturn($sourceMock);
        $sourceMock->method('getAllOptions')->willReturn($attributeData['options'] ?? []);
        $options = [];
        foreach ($attributeData['options'] as $option) {
            $optionMock = $this->getMockForAbstractClass(AttributeOptionInterface::class);
            $optionMock->method('getValue')->willReturn($option['value']);
            $optionMock->method('getLabel')->willReturn($option['label']);
            $options[] = $optionMock;
        }
        $attributeMock->method('getOptions')->willReturn($options);
        unset($attributeData['options']);
        $attributeMock->setData($attributeData);

        return $attributeMock;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function mapProvider(): array
    {
        return [
            'text' => [
                10,
                [
                    'attribute_code' => 'description',
                    'backend_type' => 'text',
                    'frontend_input' => 'text',
                    'is_searchable' => false,
                    'options' => [],
                ],
                'some text',
                ['description' => 'some text'],
            ],
            'datetime' => [
                10,
                [
                    'attribute_code' => 'created_at',
                    'backend_type' => 'datetime',
                    'frontend_input' => 'date',
                    'is_searchable' => false,
                    'options' => [],
                ],
                '00-00-00 00:00:00',
                ['created_at' => '00-00-00 00:00:00'],

            ],
            'array single value' => [
                10,
                [
                    'attribute_code' => 'attribute_array',
                    'backend_type' => 'text',
                    'frontend_input' => 'text',
                    'is_searchable' => false,
                    'options' => [],
                ],
                [10 => 'one'],
                ['attribute_array' => 'one'],
            ],
            'array multiple value' => [
                10,
                [
                    'attribute_code' => 'attribute_array',
                    'backend_type' => 'text',
                    'frontend_input' => 'text',
                    'is_searchable' => false,
                    'options' => [],
                ],
                [10 => 'one', 11 => 'two', 12 => 'three'],
                ['attribute_array' => ['one', 'two', 'three']],
            ],
            'array multiple decimal value' => [
                10,
                [
                    'attribute_code' => 'decimal_array',
                    'backend_type' => 'decimal',
                    'frontend_input' => 'text',
                    'is_searchable' => false,
                    'options' => [],
                ],
                [10 => '0.1', 11 => '0.2', 12 => '0.3'],
                ['decimal_array' => ['0.1', '0.2', '0.3']],
            ],
            'array excluded from merge' => [
                10,
                [
                    'attribute_code' => 'status',
                    'backend_type' => 'int',
                    'frontend_input' => 'select',
                    'is_searchable' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '2', 'label' => 'Disabled'],
                    ],
                ],
                [10 => '1', 11 => '2'],
                ['status' => 1],
            ],
            'select without options' => [
                10,
                [
                    'attribute_code' => 'color',
                    'backend_type' => 'text',
                    'frontend_input' => 'select',
                    'is_searchable' => false,
                    'options' => [],
                ],
                '44',
                ['color' => 44],
            ],
            'unsearchable select with options' => [
                10,
                [
                    'attribute_code' => 'color',
                    'backend_type' => 'text',
                    'frontend_input' => 'select',
                    'is_searchable' => false,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                    ],
                ],
                '44',
                ['color' => 44],
            ],
            'searchable select with options' => [
                10,
                [
                    'attribute_code' => 'color',
                    'backend_type' => 'text',
                    'frontend_input' => 'select',
                    'is_searchable' => true,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                    ],
                ],
                '44',
                ['color' => 44, 'color_value' => 'red'],
            ],
            'composite select with options' => [
                10,
                [
                    'attribute_code' => 'color',
                    'backend_type' => 'text',
                    'frontend_input' => 'select',
                    'is_searchable' => true,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                    ],
                ],
                [10 => '44', 11 => '45'],
                ['color' => [44, 45], 'color_value' => ['red', 'black']],
            ],
            'multiselect without options' => [
                10,
                [
                    'attribute_code' => 'multicolor',
                    'backend_type' => 'text',
                    'frontend_input' => 'multiselect',
                    'is_searchable' => false,
                    'options' => [],
                ],
                '44,45',
                ['multicolor' => [44, 45]],
            ],
            'unsearchable multiselect with options' => [
                10,
                [
                    'attribute_code' => 'multicolor',
                    'backend_type' => 'text',
                    'frontend_input' => 'multiselect',
                    'is_searchable' => false,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                    ],
                ],
                '44,45',
                ['multicolor' => [44, 45]],
            ],
            'searchable multiselect with options' => [
                10,
                [
                    'attribute_code' => 'multicolor',
                    'backend_type' => 'text',
                    'frontend_input' => 'multiselect',
                    'is_searchable' => true,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                    ],
                ],
                '44,45',
                ['multicolor' => [44, 45], 'multicolor_value' => ['red', 'black']],
            ],
            'composite multiselect with options' => [
                10,
                [
                    'attribute_code' => 'multicolor',
                    'backend_type' => 'text',
                    'frontend_input' => 'multiselect',
                    'is_searchable' => true,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                        ['value' => '46', 'label' => 'green'],
                    ],
                ],
                [10 => '44,45', 11 => '45,46'],
                ['multicolor' => [44, 45, 46], 'multicolor_value' => ['red', 'black', 'green']],
            ],
            'excluded attribute' => [
                10,
                [
                    'attribute_code' => 'price',
                    'backend_type' => 'int',
                    'frontend_input' => 'int',
                    'is_searchable' => false,
                    'options' => [],
                ],
                15,
                [],
            ],
        ];
    }
}
