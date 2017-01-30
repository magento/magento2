<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedImportExport\Test\Unit\Model\Import\Product\Type;

use \Magento\GroupedImportExport;

class GroupedTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{
    /** @var GroupedImportExport\Model\Import\Product\Type\Grouped */
    protected $grouped;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $setCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $setCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrCollectionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var []
     */
    protected $params;

    /**
     * @var GroupedImportExport\Model\Import\Product\Type\Grouped\Links|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $links;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityModel;

    protected function setUp()
    {
        parent::setUp();

        $this->setCollectionFactory = $this->getMock(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->setCollection = $this->getMock(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection',
            ['setEntityTypeFilter'],
            [],
            '',
            false
        );
        $this->setCollectionFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->setCollection)
        );
        $this->setCollection->expects($this->any())->method('setEntityTypeFilter')->will($this->returnValue([]));
        $this->attrCollectionFactory = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory',
            ['create', 'addFieldToFilter'],
            [],
            '',
            false
        );
        $this->attrCollectionFactory->expects($this->any())->method('create')->will($this->returnSelf());
        $this->attrCollectionFactory->expects($this->any())->method('addFieldToFilter')->willReturn([]);
        $this->entityModel = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product',
            ['getErrorAggregator', 'getNewSku', 'getOldSku', 'getNextBunch', 'isRowAllowedToImport', 'getRowScope'],
            [],
            '',
            false
        );
        $this->entityModel->method('getErrorAggregator')->willReturn($this->getErrorAggregatorObject());
        $this->params = [
            0 => $this->entityModel,
            1 => 'grouped'
        ];
        $this->links = $this->getMock(
            'Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links',
            [],
            [],
            '',
            false
        );
        $entityAttributes = [[
            'attribute_set_name' => 'attribute_id',
            'attribute_id' => 'attributeSetName',
        ]];
        $this->connection = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'fetchAll', 'fetchPairs', 'joinLeft', 'insertOnDuplicate', 'delete', 'quoteInto'],
            [],
            '',
            false
        );
        $this->select = $this->getMock(
            'Magento\Framework\DB\Select',
            ['from', 'where', 'joinLeft', 'getConnection'],
            [],
            '',
            false
        );
        $this->select->expects($this->any())->method('from')->will($this->returnSelf());
        $this->select->expects($this->any())->method('where')->will($this->returnSelf());
        $this->select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $this->connection->expects($this->any())->method('select')->will($this->returnValue($this->select));
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $connectionMock->expects($this->any())->method('quoteInto')->will($this->returnValue('query'));
        $this->select->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->connection->expects($this->any())->method('insertOnDuplicate')->willReturnSelf();
        $this->connection->expects($this->any())->method('delete')->willReturnSelf();
        $this->connection->expects($this->any())->method('quoteInto')->willReturn('');
        $this->connection->expects($this->any())->method('fetchAll')->will($this->returnValue($entityAttributes));
        $this->resource = $this->getMock(
            '\Magento\Framework\App\ResourceConnection',
            ['getConnection', 'getTableName'],
            [],
            '',
            false
        );
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($this->connection));
        $this->resource->expects($this->any())->method('getTableName')->will($this->returnValue('tableName'));
        $this->grouped = $this->objectManagerHelper->getObject(
            'Magento\GroupedImportExport\Model\Import\Product\Type\Grouped',
            [
                'attrSetColFac' => $this->setCollectionFactory,
                'prodAttrColFac' => $this->attrCollectionFactory,
                'resource' => $this->resource,
                'params' => $this->params,
                'links' => $this->links
            ]
        );
    }

    /**
     * Test for method saveData()
     *
     * @param array $skus
     * @param array $bunch
     *
     * @dataProvider testSaveDataProvider
     */
    public function testSaveData($skus, $bunch)
    {
        $this->entityModel->expects($this->once())->method('getNewSku')->will($this->returnValue($skus['newSku']));
        $this->entityModel->expects($this->once())->method('getOldSku')->will($this->returnValue($skus['oldSku']));
        $attributes = ['position' => ['id' => 0], 'qty' => ['id' => 0]];
        $this->links->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $this->entityModel->expects($this->at(2))->method('getNextBunch')->will($this->returnValue([$bunch]));
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->will($this->returnValue(true));
        $this->entityModel->expects($this->any())->method('getRowScope')->will($this->returnValue(
            \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT
        ));

        $this->links->expects($this->once())->method('saveLinksData');
        $this->grouped->saveData();
    }

    /**
     * Data provider for saveData()
     *
     * @return array
     */
    public function testSaveDataProvider()
    {
        return [
            [
                'skus' => [
                    'newSku' => [
                        'sku_assoc1' => ['entity_id' => 1],
                        'productSku' => ['entity_id' => 3, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => ['sku_assoc2' => ['entity_id' => 2]]
                ],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
                    'sku' => 'productSku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => [
                    'newSku' => [
                        'productSku' => ['entity_id' => 1, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => []
                ],
                'bunch' => [
                    'associated_skus' => '',
                    'sku' => 'productSku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => ['newSku' => [],'oldSku' => []],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
                    'sku' => 'productSku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => [
                    'newSku' => [
                        'sku_assoc1' => ['entity_id' => 1],
                        'productSku' => ['entity_id' => 3, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => []
                ],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1',
                    'sku' => 'productSku',
                    'product_type' => 'simple'
                ]
            ]
        ];
    }

    /**
     * Test saveData() with store row scope
     */
    public function testSaveDataScopeStore()
    {
        $this->entityModel->expects($this->once())->method('getNewSku')->will($this->returnValue([
            'sku_assoc1' => ['entity_id' => 1],
            'productSku' => ['entity_id' => 2, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
        ]));
        $this->entityModel->expects($this->once())->method('getOldSku')->will($this->returnValue([
            'sku_assoc2' => ['entity_id' => 3]
        ]));
        $attributes = ['position' => ['id' => 0], 'qty' => ['id' => 0]];
        $this->links->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $bunch = [[
            'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
            'sku' => 'productSku',
            'product_type' => 'grouped'
        ]];
        $this->entityModel->expects($this->at(2))->method('getNextBunch')->will($this->returnValue($bunch));
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->will($this->returnValue(true));
        $this->entityModel->expects($this->at(4))->method('getRowScope')->will($this->returnValue(
            \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT
        ));
        $this->entityModel->expects($this->at(5))->method('getRowScope')->will($this->returnValue(
            \Magento\CatalogImportExport\Model\Import\Product::SCOPE_STORE
        ));

        $this->links->expects($this->once())->method('saveLinksData');
        $this->grouped->saveData();
    }
}
