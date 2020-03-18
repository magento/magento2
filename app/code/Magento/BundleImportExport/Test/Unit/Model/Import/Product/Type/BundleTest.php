<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\BundleImportExport\Model\Import\Product\Type\Bundle;
use Magento\BundleImportExport\Model\Import\Product\Type\Bundle\RelationsDataSaver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeProductCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Test for \Magento\BundleImportExport\Model\Import\Product\Type\Bundle.
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleTest extends AbstractImportTestCase
{
    /**
     * @var Bundle
     */
    private $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var Product|MockObject
     */
    private $entityModel;

    /**
     * @var array
     */
    private $params;

    /** @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var CollectionFactory|MockObject
     */
    private $attributeSetCollectionFactoryMock;

    /**
     * @var AttributeProductCollectionFactory|MockObject
     */
    private $productAttributeCollectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private $setCollectionMock;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolverMock;

    /**
     * @var RelationsDataSaver|MockObject
     */
    private $relationsDataSaverMock;

    /**
     * Fetch all calls
     *
     * @return void
     */
    private function initFetchAllCalls(): void
    {
        $fetchAllForInitAttributes = [
            [
                'attribute_set_name' => '1',
                'attribute_id' => '1',
            ],
            [
                'attribute_set_name' => '2',
                'attribute_id' => '2',
            ],
        ];

        $fetchAllForOtherCalls = [
            [
                'selection_id' => '1',
                'option_id' => '1',
                'parent_product_id' => '1',
                'product_id' => '1',
                'position' => '1',
                'is_default' => '1',
            ],
        ];

        $this->connection->method('fetchAll')
            ->with($this->select)
            ->will(
                $this->onConsecutiveCalls(
                    $fetchAllForInitAttributes,
                    $fetchAllForOtherCalls,
                    $fetchAllForInitAttributes,
                    $fetchAllForOtherCalls,
                    $fetchAllForInitAttributes,
                    $fetchAllForOtherCalls,
                    $fetchAllForInitAttributes,
                    $fetchAllForOtherCalls,
                    $fetchAllForInitAttributes,
                    $fetchAllForInitAttributes,
                    $fetchAllForInitAttributes,
                    $fetchAllForInitAttributes,
                    $fetchAllForInitAttributes
                )
            );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        parent::setUp();

        $this->entityModel = $this->createPartialMock(
            Product::class,
            [
                'getErrorAggregator',
                'getBehavior',
                'getNewSku',
                'getNextBunch',
                'isRowAllowedToImport',
                'getRowScope',
                'getConnection'
            ]
        );
        $this->entityModel->method('getErrorAggregator')
            ->willReturn($this->getErrorAggregatorObject());

        $this->select = $this->createMock(Select::class);
        $this->select->method('from')
            ->will($this->returnSelf());
        $this->select->method('where')
            ->will($this->returnSelf());
        $this->select->method('joinLeft')
            ->will($this->returnSelf());
        $this->select->method('getConnection')
            ->willReturn($this->connection);

        $this->connection = $this->createPartialMock(
            Mysql::class,
            ['select', 'fetchAll', 'fetchPairs', 'joinLeft', 'insertOnDuplicate', 'delete', 'quoteInto', 'fetchAssoc']
        );
        $this->select = $this->createMock(Select::class);
        $this->select->method('from')
            ->will($this->returnSelf());
        $this->select->method('where')
            ->will($this->returnSelf());
        $this->select->method('joinLeft')
            ->will($this->returnSelf());
        $this->select->method('getConnection')
            ->willReturn($this->connection);
        $this->connection->method('select')
            ->willReturn($this->select);
        $this->connection->method('insertOnDuplicate')
            ->willReturnSelf();
        $this->connection->method('delete')
            ->willReturnSelf();
        $this->connection->method('quoteInto')
            ->willReturn('');

        $this->initFetchAllCalls();

        $this->resource = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->resource->method('getConnection')
            ->willReturn($this->connection);
        $this->resource->method('getTableName')
            ->willReturn('tableName');

        $this->attributeSetCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->setCollectionMock = $this->createPartialMock(
            Collection::class,
            ['setEntityTypeFilter']
        );
        $this->setCollectionMock->method('setEntityTypeFilter')
            ->willReturn([]);

        $this->attributeSetCollectionFactoryMock->method('create')
            ->willReturn($this->setCollectionMock);

        $attrCollection = $this->createMock(AttributeProductCollection::class);
        $attrCollection->method('addFieldToFilter')
            ->willReturn([]);

        $this->productAttributeCollectionFactoryMock = $this->createPartialMock(
            AttributeProductCollectionFactory::class,
            ['create']
        );
        $this->productAttributeCollectionFactoryMock->method('create')
            ->willReturn($attrCollection);

        $this->params = [
            0 => $this->entityModel,
            1 => 'bundle',
        ];

        $this->relationsDataSaverMock = $this->createMock(RelationsDataSaver::class);

        $this->scopeResolverMock = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScope'])
            ->getMockForAbstractClass();

        $this->model = $this->objectManagerHelper->getObject(
            Bundle::class,
            [
                'attrSetColFac' => $this->attributeSetCollectionFactoryMock,
                'prodAttrColFac' => $this->productAttributeCollectionFactoryMock,
                'resource' => $this->resource,
                'params' => $this->params,
                'relationsDataSaver' =>  $this->relationsDataSaverMock,
                'scopeResolver' => $this->scopeResolverMock,
            ]
        );

        $metadataMock = $this->createMock(EntityMetadata::class);
        $metadataMock->method('getLinkField')
            ->willReturn('entity_id');

        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $metadataPoolMock->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($metadataMock);

        $reflection = new \ReflectionClass(Bundle::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $metadataPoolMock);
    }

    /**
     * Save product data and check catalog product relations
     *
     * @return void
     */
    public function testRelationsSaveData(): void
    {
        $data = [
            'sku' => ['sku' => 'sku', 'entity_id' => 1, 'type_id' => 'bundle'],
            'sku1' => ['sku1' => 'sku1', 'entity_id' => 2, 'type_id' => 'bundle'],
        ];
        $bunch = [
            'sku' => 'sku',
            'name' => 'name',
            'bundle_values' => 'name=Bundle1,'
                . 'type=select,'
                . 'sku=1,'
                . 'price=10,'
                . 'price_type=fixed,'
                . 'shipment_type=separately,'
                . 'product_id=2,'
                . 'parent_product_id=1,'
        ];

        $this->entityModel->expects($this->once())
            ->method('getNewSku')
            ->willReturn($data);
        $this->entityModel->expects($this->at(2))
            ->method('getNextBunch')
            ->willReturn([$bunch]);
        $this->entityModel->expects($this->once())
            ->method('isRowAllowedToImport')
            ->willReturn(true);
        $this->connection->expects($this->exactly(2))
            ->method('fetchAssoc')
            ->with($this->select)
            ->willReturn([
                '1' => [
                    'option_id' => '1',
                    'parent_id' => '1',
                    'required' => '1',
                    'position' => '1',
                    'type' => 'bundle',
                    'value_id' => '1',
                    'title' => 'Bundle1',
                    'name' => 'bundle1',
                ],
            ]);
        $this->connection->expects($this->once())
            ->method('fetchPairs')
            ->willReturn([1 => 2]);
        $this->relationsDataSaverMock->expects($this->once())
            ->method('saveProductRelations')
            ->with(1, [2]);

        $bundle = $this->model->saveData();
        $this->assertNotNull($bundle);
    }

    /**
     * Test for method saveData()
     *
     * @param array $skus
     * @param array $bunch
     * @param bool $allowImport
     * @return void
     * @dataProvider saveDataProvider
     */
    public function testSaveData(array $skus, array $bunch, bool $allowImport): void
    {
        $this->entityModel->expects($this->once())
            ->method('getBehavior')
            ->willReturn(Import::BEHAVIOR_APPEND);
        $this->entityModel->expects($this->once())
            ->method('getNewSku')
            ->willReturn($skus['newSku']);
        $this->entityModel->expects($this->at(2))
            ->method('getNextBunch')
            ->willReturn([$bunch]);
        $this->entityModel->expects($this->once())
            ->method('isRowAllowedToImport')
            ->willReturn($allowImport);
        $this->connection->method('fetchAssoc')
            ->with($this->select)
            ->willReturn([
                '1' => [
                    'option_id' => '1',
                    'parent_id' => '1',
                    'required' => '1',
                    'position' => '1',
                    'type' => 'bundle',
                    'value_id' => '1',
                    'title' => 'Bundle1',
                    'name' => 'bundle1',
                    'selections' => [
                        [
                            'name' => 'Bundlen1',
                            'type' => 'dropdown',
                            'required' => '1',
                            'sku' => '1',
                            'price' => '10',
                            'price_type' => 'fixed',
                            'shipment_type' => '1',
                            'default_qty' => '1',
                            'is_default' => '1',
                            'position' => '1',
                            'option_id' => '1',
                        ],
                    ],
                ],
                '2' => [
                    'option_id' => '6',
                    'parent_id' => '6',
                    'required' => '6',
                    'position' => '6',
                    'type' => 'bundle',
                    'value_id' => '6',
                    'title' => 'Bundle6',
                    'selections' => [
                        [
                            'name' => 'Bundlen6',
                            'type' => 'dropdown',
                            'required' => '1',
                            'sku' => '222',
                            'price' => '10',
                            'price_type' => 'percent',
                            'shipment_type' => 0,
                            'default_qty' => '2',
                            'is_default' => '1',
                            'position' => '6',
                            'option_id' => '6',
                        ],
                    ],
                ],
            ]);

        $bundle = $this->model->saveData();
        $this->assertNotNull($bundle);
    }

    /**
     * Data provider for saveData()
     *
     * @return array
     */
    public function saveDataProvider(): array
    {
        return [
            [
                'skus' => ['newSku' => ['sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'bundle']]],
                'bunch' => ['bundle_values' => 'value1', 'sku' => 'sku', 'name' => 'name'],
                'allowImport' => true,
            ],
            [
                'skus' => ['newSku' => ['sku' => ['sku' => 'SKU', 'entity_id' => 3, 'type_id' => 'bundle']]],
                'bunch' => ['bundle_values' => 'value1', 'sku' => 'SKU', 'name' => 'name'],
                'allowImport' => true,
            ],
            [
                'skus' => ['newSku' => ['sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'simple']]],
                'bunch' => ['bundle_values' => 'value1', 'sku' => 'sku', 'name' => 'name'],
                'allowImport' => true,
            ],
            [
                'skus' => ['newSku' => ['sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'bundle']]],
                'bunch' => ['bundle_values' => 'value1', 'sku' => 'sku', 'name' => 'name'],
                'allowImport' => false,
            ],
            'Import without bundle values' => [
                'skus' => ['newSku' => ['sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'bundle']]],
                'bunch' => ['sku' => 'sku', 'name' => 'name'],
                'allowImport' => true,
            ],
            [
                'skus' => ['newSku' => [
                    'sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'bundle'],
                    'sku1' => ['sku1' => 'sku1', 'entity_id' => 3, 'type_id' => 'bundle'],
                    'sku2' => ['sku2' => 'sku2', 'entity_id' => 3, 'type_id' => 'bundle'],
                ]],
                'bunch' => [
                    'sku' => 'sku',
                    'name' => 'name',
                    'bundle_values' => 'name=Bundle1,'
                         . 'type=dropdown,'
                         . 'required=1,'
                         . 'sku=1,'
                         . 'price=10,'
                         . 'price_type=fixed,'
                         . 'shipment_type=separately,'
                         . 'default_qty=1,'
                         . 'is_default=1,'
                         . 'position=1,'
                         . 'option_id=1 | name=Bundle2,'
                         . 'type=dropdown,'
                         . 'required=1,'
                         . 'sku=2,'
                         . 'price=10,'
                         . 'price_type=fixed,'
                         . 'default_qty=1,'
                         . 'is_default=1,'
                         . 'position=2,'
                         . 'option_id=2'
                ],
                'allowImport' => true,
            ],
        ];
    }

    /**
     * Test for method saveData()
     *
     * @return void
     */
    public function testSaveDataDelete(): void
    {
        $this->entityModel->expects($this->once())
            ->method('getBehavior')
            ->willReturn(Import::BEHAVIOR_DELETE);
        $this->entityModel->expects($this->once())
            ->method('getNewSku')
            ->willReturn([
                'sku' => ['sku' => 'sku', 'entity_id' => 3, 'attr_set_code' => 'Default', 'type_id' => 'bundle']
            ]);
        $this->entityModel->expects($this->at(2))
            ->method('getNextBunch')
            ->willReturn([
                ['bundle_values' => 'value1', 'sku' => 'sku', 'name' => 'name']
            ]);
        $select = $this->createMock(Select::class);
        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($select);

        $this->connection->expects($this->once())
            ->method('fetchAssoc')
            ->with($select)->willReturn([
                ['id1', 'id2', 'id_3']
            ]);

        $bundle = $this->model->saveData();
        $this->assertNotNull($bundle);
    }

    /**
     * Prepare attributes with defaultVlue inside call
     *
     * @return void
     */
    public function testPrepareAttributesWithDefaultValueForSaveInsideCall(): void
    {
        $bundleMock = $this->createPartialMock(
            Bundle::class,
            ['transformBundleCustomAttributes']
        );
        // Set some attributes to bypass errors due to static call inside method.
        $attrVal = 'value';
        $rowData = [
            Product::COL_ATTR_SET => $attrVal,
        ];
        $this->setPropertyValue($bundleMock, '_attributes', [
            $attrVal => [],
        ]);

        $bundleMock->expects($this->once())
            ->method('transformBundleCustomAttributes')
            ->with($rowData)
            ->willReturn([]);

        $bundleMock->prepareAttributesWithDefaultValueForSave($rowData);
    }

    /**
     * Test for isRowValid()
     *
     * @return void
     */
    public function testIsRowValid(): void
    {
        $this->entityModel->expects($this->once())
            ->method('getRowScope')
            ->willReturn(-1);
        $rowData = [
            'bundle_price_type' => 'dynamic',
            'bundle_shipment_type' => 'separately',
            'bundle_price_view' => 'bundle_price_view'
        ];

        $this->assertEquals($this->model->isRowValid($rowData, 0), true);
    }

    /**
     * Set property to object
     *
     * @param $object
     * @param string $property
     * @param array $value
     * @return MockObject
     */
    private function setPropertyValue(&$object, string $property, array $value): MockObject
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }
}
