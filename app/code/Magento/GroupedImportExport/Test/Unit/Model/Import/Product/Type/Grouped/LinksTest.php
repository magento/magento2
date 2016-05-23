<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedImportExport\Test\Unit\Model\Import\Product\Type\Grouped;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class LinksTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links */
    protected $links;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Link|\PHPUnit_Framework_MockObject_MockObject */
    protected $link;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql */
    protected $connection;

    /** @var \Magento\ImportExport\Model\ImportFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $importFactory;

    /** @var \Magento\ImportExport\Model\Import|\PHPUnit_Framework_MockObject_MockObject */
    protected $import;

    protected function setUp()
    {
        $this->link = $this->getMock('Magento\Catalog\Model\ResourceModel\Product\Link', [], [], '', false);
        $this->connection = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $this->resource = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->resource
            ->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->connection));

        $this->import = $this->getMock('Magento\ImportExport\Model\Import', [], [], '', false);
        $this->importFactory = $this->getMock('Magento\ImportExport\Model\ImportFactory', ['create'], [], '', false);
        $this->importFactory->expects($this->any())->method('create')->will($this->returnValue($this->import));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->links = $this->objectManagerHelper->getObject(
            'Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links',
            [
                'productLink' => $this->link,
                'resource' => $this->resource,
                'importFactory' => $this->importFactory
            ]
        );
    }

    public function linksDataProvider()
    {
        return [
            [
                'linksData' => [
                    'product_ids' => [1, 2],
                    'relation' => [],
                    'attr_product_ids' => []
                ]
            ]
        ];
    }

    /**
     * @param array $linksData
     *
     * @dataProvider linksDataProvider
     */
    public function testSaveLinksDataNoProductsAttrs($linksData)
    {
        $this->processBehaviorGetter('append');
        $attributes = $this->attributesDataProvider();
        $this->processAttributeGetter($attributes[2]['dbAttributes']);
        $this->connection->expects($this->exactly(2))->method('insertOnDuplicate');
        $this->links->saveLinksData($linksData);
    }

    /**
     * @param array $linksData
     *
     * @dataProvider linksDataProvider
     */
    public function testSaveLinksDataWithProductsAttrs($linksData)
    {
        $linksData['attr_product_ids'] = [12 => true, 16 => true];
        $linksData['position'] = [4 => 6];
        $linksData['qty'] = [9 => 3];
        $this->processBehaviorGetter('append');
        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->connection->expects($this->any())->method('select')->will($this->returnValue($select));
        $select->expects($this->any())->method('from')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());
        $this->connection->expects($this->once())->method('fetchAll')->with($select)->will($this->returnValue([]));
        $this->connection->expects($this->once())->method('fetchPairs')->with($select)->will(
            $this->returnValue([])
        );
        $this->connection->expects($this->exactly(4))->method('insertOnDuplicate');
        $this->links->saveLinksData($linksData);
    }

    public function attributesDataProvider()
    {
        return [
            [
                'dbAttributes' => [],
                'returnedAttributes' => null
            ],
            [
                'dbAttributes' => [
                    ['code' => 2, 'id' => 6, 'type' => 'sometable']
                ],
                'returnedAttributes' => [
                    2 => ['id' => 6, 'table' => 'table_name']
                ]
            ],
            [
                'dbAttributes' => [
                    ['code' => 8, 'id' => 11, 'type' => 'sometable1'],
                    ['code' => 4, 'id' => 7, 'type' => 'sometable2']
                ],
                'returnedAttributes' => [
                    4 => ['id' => 7, 'table' => 'table_name'],
                    8 => ['id' => 11, 'table' => 'table_name']
                ]
            ]
        ];
    }

    protected function processAttributeGetter($dbAttributes)
    {
        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->connection->expects($this->once())->method('select')->will($this->returnValue($select));
        $select->expects($this->once())->method('from')->will($this->returnSelf());
        $select->expects($this->once())->method('where')->will($this->returnSelf());
        $this->connection->expects($this->once())->method('fetchAll')->with($select)->will(
            $this->returnValue($dbAttributes)
        );
        $this->link->expects($this->any())->method('getAttributeTypeTable')->will(
            $this->returnValue('table_name')
        );
    }

    /**
     * @param array $dbAttributes
     * @param array $returnedAttributes
     *
     * @dataProvider attributesDataProvider
     */
    public function testGetAttributes($dbAttributes, $returnedAttributes)
    {
        $this->processAttributeGetter($dbAttributes);
        $actualAttributes = $this->links->getAttributes();
        $this->assertEquals($returnedAttributes, $actualAttributes);
    }

    protected function processBehaviorGetter($behavior)
    {
        $dataSource = $this->getMock('Magento\ImportExport\Model\ResourceModel\Import\Data', [], [], '', false);
        $dataSource->expects($this->once())->method('getBehavior')->will($this->returnValue($behavior));
        $this->import->expects($this->once())->method('getDataSourceModel')->will($this->returnValue($dataSource));
    }
}
