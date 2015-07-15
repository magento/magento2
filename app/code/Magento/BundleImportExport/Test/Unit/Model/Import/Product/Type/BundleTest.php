<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleImportExport\Test\Unit\Model\Import\Product\Type;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\BundleImportExport\Model\Import\Product\Type\Bundle
     */
    protected $bundle;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityModel;

    /**
     * @var []
     */
    protected $params;

    /** @var
     * \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrSetColFac;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $prodAttrColFac;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $setCollection;

    protected function setUp()
    {
        $this->entityModel = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product',
            ['getBehavior', 'getNewSku', 'getNextBunch', 'isRowAllowedToImport', 'getRowScope', 'getConnection'],
            [],
            '',
            false
        );
        $this->connection = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'fetchAll', 'fetchPairs', 'joinLeft', 'insertOnDuplicate', 'delete', 'quoteInto', 'fetchAssoc'],
            [],
            '',
            false
        );
        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $select->expects($this->any())->method('from')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());
        $select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $adapter = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $adapter->expects($this->any())->method('quoteInto')->will($this->returnValue('query'));
        $select->expects($this->any())->method('getAdapter')->willReturn($adapter);
        $this->connection->expects($this->any())->method('select')->will($this->returnValue($select));
        $this->connection->expects($this->any())->method('fetchPairs')->will($this->returnValue([
            '1' => '1', '2' => '2'
        ]));
        $this->connection->expects($this->any())->method('insertOnDuplicate')->willReturnSelf();
        $this->connection->expects($this->any())->method('delete')->willReturnSelf();
        $this->connection->expects($this->any())->method('quoteInto')->willReturn('');
        $this->resource = $this->getMock(
            'Magento\Framework\App\Resource',
            ['getConnection', 'getTableName'],
            [],
            '',
            false
        );
        $this->resource->expects($this->any())->method('getConnection')->will(
            $this->returnValue($this->connection)
        );
        $this->resource->expects($this->any())->method('getTableName')->will(
            $this->returnValue('tableName')
        );
        $this->attrSetColFac = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->setCollection = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection',
            ['setEntityTypeFilter'],
            [],
            '',
            false
        );
        $this->attrSetColFac->expects($this->any())->method('create')->will(
            $this->returnValue($this->setCollection)
        );
        $this->setCollection->expects($this->any())
            ->method('setEntityTypeFilter')
            ->will($this->returnValue([]));
        $this->prodAttrColFac = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $attrCollection =
            $this->getMock('\Magento\Catalog\Model\Resource\Product\Attribute\Collection', [], [], '', false);
        $attrCollection->expects($this->any())->method('addFieldToFilter')->willReturn([]);

        $this->prodAttrColFac->expects($this->any())->method('create')->will(
            $this->returnValue($attrCollection)
        );
        $this->params = [
            0 => $this->entityModel,
            1 => 'bundle'
        ];
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->bundle = $this->objectManagerHelper->getObject(
            'Magento\BundleImportExport\Model\Import\Product\Type\Bundle',
            [
                'attrSetColFac' => $this->attrSetColFac,
                'prodAttrColFac' => $this->prodAttrColFac,
                'resource' => $this->resource,
                'params' => $this->params
            ]
        );
    }

    /**
     * Test for method saveData()
     *
     * @param array $skus
     * @param array $bunch
     * @param $allowImport
     * @dataProvider testSaveDataProvider
     */
    public function testSaveData($skus, $bunch, $allowImport)
    {
        $this->entityModel->expects($this->any())->method('getBehavior')->will($this->returnValue(
            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND
        ));
        $this->entityModel->expects($this->once())->method('getNewSku')->will($this->returnValue($skus['newSku']));
        $this->entityModel->expects($this->at(2))->method('getNextBunch')->will($this->returnValue([$bunch]));
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->will($this->returnValue(
            $allowImport
        ));

        $adapter = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $adapter->expects($this->any())->method('quoteInto')->will($this->returnValue('query'));

        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $select->expects($this->any())->method('getAdapter')->willReturn($adapter);
        $select->expects($this->any())->method('from')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());
        $select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $this->connection->expects($this->any())->method('select')->will($this->returnValue($select));

        $this->connection->expects($this->any())->method('fetchAssoc')->with($select)->will($this->returnValue([
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
                    ['name' => 'Bundlen1',
                        'type' => 'dropdown',
                        'required' => '1',
                        'sku' => '1',
                        'price' => '10',
                        'price_type' => 'fixed',
                        'default_qty' => '1',
                        'is_defaul' => '1',
                        'position' => '1',
                        'option_id' => '1']
                ]
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
                    ['name' => 'Bundlen6',
                        'type' => 'dropdown',
                        'required' => '1',
                        'sku' => '222',
                        'price' => '10',
                        'price_type' => 'percent',
                        'default_qty' => '2',
                        'is_defaul' => '1',
                        'position' => '6',
                        'option_id' => '6']
                ]
            ]
        ]));
        $this->connection->expects($this->any())->method('fetchAll')->with($select)->will($this->returnValue([[
            'selection_id' => '1',
            'option_id' => '1',
            'parent_product_id' => '1',
            'product_id' => '1',
            'position' => '1',
            'is_default' => '1'
        ]]));

        $this->bundle->saveData();
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
                'skus' => ['newSku' => ['sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'bundle']]],
                'bunch' => ['bundle_values' => 'value1', 'sku' => 'sku', 'name' => 'name'],
                'allowImport' => true
            ],
            [
                'skus' => ['newSku' => ['sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'simple']]],
                'bunch' => ['bundle_values' => 'value1', 'sku' => 'sku', 'name' => 'name'],
                'allowImport' => true
            ],
            [
                'skus' => ['newSku' => ['sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'bundle']]],
                'bunch' => ['bundle_values' => 'value1', 'sku' => 'sku', 'name' => 'name'],
                'allowImport' => false
            ],
            [
                'skus' => ['newSku' => [
                    'sku' => ['sku' => 'sku', 'entity_id' => 3, 'type_id' => 'bundle'],
                    'sku1' => ['sku1' => 'sku1', 'entity_id' => 3, 'type_id' => 'bundle'],
                    'sku2' => ['sku2' => 'sku2', 'entity_id' => 3, 'type_id' => 'bundle']
                ]],
                'bunch' => [
                    'sku' => 'sku',
                    'name' => 'name',
                    'bundle_values' =>
                        'name=Bundle1,'
                         . 'type=dropdown,'
                         . 'required=1,'
                         . 'sku=1,'
                         . 'price=10,'
                         . 'price_type=fixed,'
                         . 'default_qty=1,'
                         . 'is_defaul=1,'
                         . 'position=1,'
                         . 'option_id=1 | name=Bundle2,'
                         . 'type=dropdown,'
                         . 'required=1,'
                         . 'sku=2,'
                         . 'price=10,'
                         . 'price_type=fixed,'
                         . 'default_qty=1,'
                         . 'is_defaul=1,'
                         . 'position=2,'
                         . 'option_id=2'
                ],
                'allowImport' => true
            ]
        ];
    }

    /**
     * Test for method saveData()
     */
    public function testSaveDataDelete()
    {
        $this->entityModel->expects($this->any())->method('getBehavior')->will($this->returnValue(
            \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE
        ));
        $this->entityModel->expects($this->once())->method('getNewSku')->will($this->returnValue([
            'sku' => ['sku' => 'sku', 'entity_id' => 3, 'attr_set_code' => 'Default', 'type_id' => 'bundle']
        ]));
        $this->entityModel->expects($this->at(2))->method('getNextBunch')->will($this->returnValue([
            ['bundle_values' => 'value1', 'sku' => 'sku', 'name' => 'name']
        ]));
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->will($this->returnValue(true));
        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->connection->expects($this->any())->method('select')->will($this->returnValue($select));
        $select->expects($this->any())->method('from')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());
        $select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $this->connection->expects($this->any())->method('fetchAssoc')->with($select)->will($this->returnValue([
            ['id1', 'id2', 'id_3']
        ]));
        $this->bundle->saveData();
    }

    public function testPrepareAttributesWithDefaultValueForSaveInsideCall()
    {
        $bundleMock = $this->getMock(
            'Magento\BundleImportExport\Model\Import\Product\Type\Bundle',
            ['transformBundleCustomAttributes'],
            [],
            '',
            false
        );
        // Set some attributes to bypass errors due to static call inside method.
        $attrVal = 'value';
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET => $attrVal,
        ];
        $this->setPropertyValue($bundleMock, '_attributes', [
            $attrVal => [],
        ]);

        $bundleMock
            ->expects($this->once())
            ->method('transformBundleCustomAttributes')
            ->with($rowData)
            ->willReturn([]);

        $bundleMock->prepareAttributesWithDefaultValueForSave($rowData);
    }

    /**
     * Test for isRowValid()
     */
    public function testIsRowValid()
    {
        $this->entityModel->expects($this->any())->method('getRowScope')->will($this->returnValue(-1));
        $rowData = [
            'price_type' => 'fixed',
            'price_view' => 'bundle_price_view'
        ];
        $this->assertEquals($this->bundle->isRowValid($rowData, 0), true);
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }
}
