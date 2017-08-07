<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;
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
        $this->builderMock = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\Document\Builder::class)
            ->setMethods(['addField', 'addFields', 'build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldMapperMock = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class)
            ->setMethods(['getFieldName', 'getAllAttributesTypes'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = $this->getMockBuilder(DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->additionalFieldsProvider = $this->getMockBuilder(AdditionalFieldsProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateFieldTypeMock = $this->getMockBuilder(Date::class)
            ->disableOriginalConstructor()
            ->getMock();
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

    public function testGetMapAdditionalFieldsOnly()
    {
        $productId = 42;
        $storeId = 1;
        $additionalFields = ['some data'];
        $this->builderMock->expects($this->once())->method('addField')->with('store_id', $storeId);

        $this->builderMock->expects($this->any())->method('addFields')
            ->withConsecutive([$additionalFields])
            ->will(
                $this->returnSelf()
            );
        $this->builderMock->expects($this->any())->method('build')->will(
            $this->returnValue([])
        );
        $this->additionalFieldsProvider->expects($this->once())->method('getFields')
            ->with([$productId], $storeId)
            ->willReturn([$productId => $additionalFields]);

        $this->assertEquals(
            [$productId],
            array_keys($this->model->map([$productId => []], $storeId, []))
        );
    }

    public function testGetMapEmptyData()
    {
        $storeId = 1;
        $this->builderMock->expects($this->never())->method('addField');
        $this->builderMock->expects($this->never())->method('build');
        $this->additionalFieldsProvider->expects($this->once())
            ->method('getFields')
            ->with([], $storeId)->willReturn([]);

        $this->assertEquals(
            [],
            $this->model->map([], $storeId, [])
        );
    }

    public function testGetMapWithExcludedAttribute()
    {
        $productId = 42;
        $storeId = 1;
        $productAttributeData = ['price' => 42];
        $attributeCode = 'price';
        $returnAttributeData = ['store_id' => $storeId];

        $this->dataProvider->expects($this->any())->method('getSearchableAttribute')
            ->with($attributeCode)
            ->willReturn($this->getAttribute($attributeCode, [
                'value' => 42,
                'backendType' => 'int',
                'frontendInput' => 'int',
                'options' => []
            ]));

        $this->fieldMapperMock->expects($this->never())->method('getFieldName');
        $this->builderMock->expects($this->any())
            ->method('addField')
            ->with('store_id', $storeId);
        $this->builderMock->expects($this->once())->method('build')->willReturn($returnAttributeData);

        $this->additionalFieldsProvider->expects($this->once())->method('getFields')->willReturn([]);
        $this->assertEquals(
            [$productId => $returnAttributeData],
            $this->model->map([$productId => $productAttributeData], $storeId, [])
        );
    }

    /**
     * @param int $productId
     * @param array $productData
     * @param array $returnAttributeData
     * @dataProvider mapProvider
     */
    public function testGetMap($productId, $productData, $returnAttributeData)
    {
        $storeId = 1;
        $attributeCode = $productData['attributeCode'];
        $this->dataProvider->expects($this->any())->method('getSearchableAttribute')
            ->with($attributeCode)
            ->willReturn($this->getAttribute($attributeCode, $productData['attributeData']));

        $this->fieldMapperMock->expects($this->any())->method('getFieldName')
            ->with($attributeCode, [])
            ->willReturnArgument(0);
        if ($productData['attributeData']['frontendInput'] === 'date') {
            $this->dateFieldTypeMock->expects($this->once())->method('formatDate')
                ->with($storeId, $productData['attributeValue'])
                ->willReturnArgument(1);
        }

        $this->builderMock->expects($this->exactly(2))
            ->method('addField')
            ->withConsecutive(
                ['store_id', $storeId],
                [$attributeCode, $returnAttributeData[$attributeCode]]
            );

        $this->builderMock->expects($this->once())->method('build')->willReturn($returnAttributeData);
        $this->additionalFieldsProvider->expects($this->once())->method('getFields')->willReturn([]);
        $documentData = [
            $productId => [$productData['attributeCode'] => $productData['attributeValue']]
        ];
        $this->assertEquals(
            [$productId => $returnAttributeData],
            $this->model->map($documentData, $storeId, [])
        );
    }

    /**
     * @param int $productId
     * @param array $productData
     * @param array $returnAttributeData
     * @dataProvider mapProviderForAttributeWithOptions
     */
    public function testGetMapForAttributeWithOptions($productId, $productData, $returnAttributeData)
    {
        $storeId = 1;
        $attributeCode = $productData['attributeCode'];
        $this->dataProvider->expects($this->any())->method('getSearchableAttribute')
            ->with($attributeCode)
            ->willReturn($this->getAttribute($attributeCode, $productData['attributeData']));

        $this->fieldMapperMock->expects($this->any())->method('getFieldName')
            ->with($attributeCode, [])
            ->willReturnArgument(0);
        if ($productData['attributeData']['frontendInput'] === 'date') {
            $this->dateFieldTypeMock->expects($this->once())->method('formatDate')
                ->with($storeId, $productData['attributeValue'])
                ->willReturnArgument(1);
        }
        $this->builderMock->expects($this->exactly(3))
            ->method('addField')
            ->withConsecutive(
                ['store_id', $storeId],
                [$attributeCode . '_value', $returnAttributeData[$attributeCode . '_value']],
                [$attributeCode, $returnAttributeData[$attributeCode]]
            );
        $this->builderMock->expects($this->once())->method('build')->willReturn($returnAttributeData);
        $this->additionalFieldsProvider->expects($this->once())->method('getFields')->willReturn([]);
        $documentData = [
            $productId => [$productData['attributeCode'] => $productData['attributeValue']]
        ];
        $this->assertEquals(
            [$productId => $returnAttributeData],
            $this->model->map($documentData, $storeId, [])
        );
    }

    /**
     * Return attribute mock
     *
     * @param string $attributeCode
     * @param array attributeData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getAttribute($attributeCode, $attributeData)
    {
        $attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $attribute->expects($this->once())->method('getBackendType')->willReturn($attributeData['backendType']);
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn($attributeData['frontendInput']);
        $attribute->expects($this->any())->method('getOptions')->willReturn($attributeData['options']);

        return $attribute;
    }

    /**
     * @return array
     */
    public static function mapProvider()
    {
        return [
            'text attribute' => [
                11,
                [
                    'attributeCode' => 'description',
                    'attributeValue' => 'some text',
                    'attributeData' => [
                        'backendType' => 'text',
                        'frontendInput' => 'text',
                        'options' => []
                    ]
                ],
                ['description' => 'some text'],
            ],
            'date time attribute' => [
                12,
                [
                    'attributeCode' => 'created_at',
                    'attributeValue' => '00-00-00 00:00:00',
                    'attributeData' => [
                        'backendType' => 'datetime',
                        'frontendInput' => 'date',
                        'options' => []
                    ]
                ],
                ['created_at' => '00-00-00 00:00:00'],

            ],
            'array value attribute' => [
                12,
                [
                    'attributeCode' => 'attribute_array',
                    'attributeValue' => ['one', 'two', 'three'],
                    'attributeData' => [
                        'backendType' => 'text',
                        'frontendInput' => 'text',
                        'options' => []
                    ]
                ],
                ['attribute_array' => 'one two three'],
            ],
            'multiselect value attribute' => [
                12,
                [
                    'attributeCode' => 'multiselect',
                    'attributeValue' => 'some,data with,comma',
                    'attributeData' => [
                        'backendType' => 'text',
                        'frontendInput' => 'multiselect',
                        'options' => []
                    ]
                ],
                ['multiselect' => 'some data with comma'],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function mapProviderForAttributeWithOptions()
    {
        return [
            'select value attribute' => [
                12,
                [
                    'attributeCode' => 'color',
                    'attributeValue' => '44',
                    'attributeData' => [
                        'backendType' => 'text',
                        'frontendInput' => 'select',
                        'options' => [new \Magento\Framework\DataObject(['value' => '44', 'label' => 'red'])]
                    ]
                ],
                ['color' => '44', 'color_value' => 'red'],
            ],
        ];
    }
}
