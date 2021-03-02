<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedImportExport\Test\Unit\Model\Import\Product\Type\Grouped;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class LinksTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links */
    protected $links;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Link|\PHPUnit\Framework\MockObject\MockObject */
    protected $link;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject */
    protected $resource;

    /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql */
    protected $connection;

    /** @var \Magento\ImportExport\Model\ImportFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $importFactory;

    /** @var \Magento\ImportExport\Model\Import|\PHPUnit\Framework\MockObject\MockObject */
    protected $import;

    protected function setUp(): void
    {
        $this->link = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Link::class);
        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resource
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->import = $this->createMock(\Magento\ImportExport\Model\Import::class);
        $this->importFactory = $this->createPartialMock(\Magento\ImportExport\Model\ImportFactory::class, ['create']);
        $this->importFactory->expects($this->any())->method('create')->willReturn($this->import);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->links = $this->objectManagerHelper->getObject(
            \Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links::class,
            [
                'productLink' => $this->link,
                'resource' => $this->resource,
                'importFactory' => $this->importFactory
            ]
        );
    }

    /**
     * @return array
     */
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
        $attributes = [
            ['id' => 1, 'code' => 'position', 'type' => 'int'],
            ['id' => 2, 'code' => 'qty', 'type' => 'decimal'],
        ];
        $this->processBehaviorGetter('append');
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->connection->expects($this->any())->method('select')->willReturn($select);
        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $this->connection->expects($this->once())->method('fetchAll')->with($select)->willReturn($attributes);
        $this->connection->expects($this->once())->method('fetchPairs')->with($select)->willReturn(
            []
        );
        $this->connection->expects($this->exactly(4))->method('insertOnDuplicate');
        $this->link->expects($this->exactly(2))->method('getAttributeTypeTable')->willReturn(
            'table_name'
        );
        $this->links->saveLinksData($linksData);
    }

    /**
     * @return array
     */
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

    /**
     * @param $dbAttributes
     */
    protected function processAttributeGetter($dbAttributes)
    {
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->willReturnSelf();
        $select->expects($this->once())->method('where')->willReturnSelf();
        $this->connection->expects($this->once())->method('fetchAll')->with($select)->willReturn(
            $dbAttributes
        );
        $this->link->expects($this->any())->method('getAttributeTypeTable')->willReturn(
            'table_name'
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

    /**
     * @param $behavior
     */
    protected function processBehaviorGetter($behavior)
    {
        $dataSource = $this->createMock(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);
        $dataSource->expects($this->once())->method('getBehavior')->willReturn($behavior);
        $this->import->expects($this->once())->method('getDataSourceModel')->willReturn($dataSource);
    }
}
