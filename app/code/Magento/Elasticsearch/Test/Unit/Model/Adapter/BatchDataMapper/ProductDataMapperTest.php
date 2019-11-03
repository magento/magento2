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
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldType\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ProductDataMapperTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductDataMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductDataMapper
     */
    private $model;

    /**
     * @var Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $builderMock;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldMapperMock;

    /**
     * @var Date|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateFieldTypeMock;

    /**
     * @var AdditionalFieldsProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $additionalFieldsProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProvider;

    /**
     * Set up test environment.
     */
    protected function setUp()
    {
        $this->builderMock = $this->createTestProxy(Builder::class);
        $this->fieldMapperMock = $this->createMock(FieldMapperInterface::class);
        $this->dataProvider = $this->createMock(DataProvider::class);
        $this->attribute = $this->createMock(Attribute::class);
        $this->additionalFieldsProvider = $this->createMock(AdditionalFieldsProviderInterface::class);
        $this->dateFieldTypeMock = $this->createMock(Date::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            ProductDataMapper::class,
            [
                'builder' => $this->builderMock,
                'fieldMapper' => $this->fieldMapperMock,
                'dateFieldType' => $this->dateFieldTypeMock,
                'dataProvider' => $this->dataProvider,
                'additionalFieldsProvider' => $this->additionalFieldsProvider,
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
            ->will($this->returnSelf());
        $this->builderMock->expects($this->any())
            ->method('build')
            ->will($this->returnValue([]));
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
        $returnAttributeData['store_id'] = $storeId;
        $this->assertEquals($returnAttributeData, $documents[$productId]);
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getAttribute(array $attributeData): \PHPUnit_Framework_MockObject_MockObject
    {
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeData['code']);
        $attributeMock->method('getBackendType')->willReturn($attributeData['backendType']);
        $attributeMock->method('getFrontendInput')->willReturn($attributeData['frontendInput']);
        $attributeMock->method('getIsSearchable')->willReturn($attributeData['is_searchable']);
        $options = [];
        foreach ($attributeData['options'] as $option) {
            $optionMock = $this->createMock(AttributeOptionInterface::class);
            $optionMock->method('getValue')->willReturn($option['value']);
            $optionMock->method('getLabel')->willReturn($option['label']);
            $options[] = $optionMock;
        }
        $attributeMock->method('getOptions')->willReturn($options);

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
                    'code' => 'description',
                    'backendType' => 'text',
                    'frontendInput' => 'text',
                    'is_searchable' => false,
                    'options' => [],
                ],
                'some text',
                ['description' => 'some text'],
            ],
            'datetime' => [
                10,
                [
                    'code' => 'created_at',
                    'backendType' => 'datetime',
                    'frontendInput' => 'date',
                    'is_searchable' => false,
                    'options' => [],
                ],
                '00-00-00 00:00:00',
                ['created_at' => '00-00-00 00:00:00'],

            ],
            'array single value' => [
                10,
                [
                    'code' => 'attribute_array',
                    'backendType' => 'text',
                    'frontendInput' => 'text',
                    'is_searchable' => false,
                    'options' => [],
                ],
                [10 => 'one'],
                ['attribute_array' => 'one'],
            ],
            'array multiple value' => [
                10,
                [
                    'code' => 'attribute_array',
                    'backendType' => 'text',
                    'frontendInput' => 'text',
                    'is_searchable' => false,
                    'options' => [],
                ],
                [10 => 'one', 11 => 'two', 12 => 'three'],
                ['attribute_array' => ['one', 'two', 'three']],
            ],
            'array multiple decimal value' => [
                10,
                [
                    'code' => 'decimal_array',
                    'backendType' => 'decimal',
                    'frontendInput' => 'text',
                    'is_searchable' => false,
                    'options' => [],
                ],
                [10 => '0.1', 11 => '0.2', 12 => '0.3'],
                ['decimal_array' => ['0.1', '0.2', '0.3']],
            ],
            'array excluded from merge' => [
                10,
                [
                    'code' => 'status',
                    'backendType' => 'int',
                    'frontendInput' => 'select',
                    'is_searchable' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '2', 'label' => 'Disabled'],
                    ],
                ],
                [10  => '1', 11 => '2'],
                ['status' => '1'],
            ],
            'select without options' => [
                10,
                [
                    'code' => 'color',
                    'backendType' => 'text',
                    'frontendInput' => 'select',
                    'is_searchable' => false,
                    'options' => [],
                ],
                '44',
                ['color' => '44'],
            ],
            'unsearchable select with options' => [
                10,
                [
                    'code' => 'color',
                    'backendType' => 'text',
                    'frontendInput' => 'select',
                    'is_searchable' => false,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                    ],
                ],
                '44',
                ['color' => '44'],
            ],
            'searchable select with options' => [
                10,
                [
                    'code' => 'color',
                    'backendType' => 'text',
                    'frontendInput' => 'select',
                    'is_searchable' => true,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                    ],
                ],
                '44',
                ['color' => '44', 'color_value' => 'red'],
            ],
            'composite select with options' => [
                10,
                [
                    'code' => 'color',
                    'backendType' => 'text',
                    'frontendInput' => 'select',
                    'is_searchable' => true,
                    'options' => [
                        ['value' => '44', 'label' => 'red'],
                        ['value' => '45', 'label' => 'black'],
                    ],
                ],
                [10 => '44', 11 => '45'],
                ['color' => ['44', '45'], 'color_value' => ['red', 'black']],
            ],
            'multiselect without options' => [
                10,
                [
                    'code' => 'multicolor',
                    'backendType' => 'text',
                    'frontendInput' => 'multiselect',
                    'is_searchable' => false,
                    'options' => [],
                ],
                '44,45',
                ['multicolor' => [44, 45]],
            ],
            'unsearchable multiselect with options' => [
                10,
                [
                    'code' => 'multicolor',
                    'backendType' => 'text',
                    'frontendInput' => 'multiselect',
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
                    'code' => 'multicolor',
                    'backendType' => 'text',
                    'frontendInput' => 'multiselect',
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
                    'code' => 'multicolor',
                    'backendType' => 'text',
                    'frontendInput' => 'multiselect',
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
                    'code' => 'price',
                    'backendType' => 'int',
                    'frontendInput' => 'int',
                    'is_searchable' => false,
                    'options' => []
                ],
                15,
                []
            ],
        ];
    }
}
