<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

use Magento\Framework\Indexer\GridStructure;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class GridStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flatScopeResolver;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var GridStructure
     */
    protected $object;

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->getMock();
        $this->resource = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->flatScopeResolver = $this->getMockBuilder('Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->with('write')
            ->willReturn($this->connection);
        $this->object = new GridStructure(
            $this->resource,
            $this->flatScopeResolver
        );
    }

    public function testDelete()
    {
        $index = 'index';
        $table = 'index_table';

        $this->flatScopeResolver->expects($this->once())
            ->method('resolve')
            ->with($index, [])
            ->willReturn($table);
        $this->connection->expects($this->once())
            ->method('isTableExists')
            ->with($table)
            ->willReturn(true);
        $this->connection->expects($this->once())
            ->method('dropTable')
            ->with($table);

        $this->object->delete($index);
    }

    public function testCreate()
    {
        $index = 'index';
        $fields = [
            [
                'type'     => 'searchable',
                'name'     => 'field',
                'dataType' => 'int'
            ]
        ];
        $tableName = 'index_table';
        $idxName = 'idxName';

        $table = $this->getMockBuilder('Magento\Framework\DB\Ddl\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $this->flatScopeResolver->expects($this->once())
            ->method('resolve')
            ->with($index, [])
            ->willReturn($tableName);
        $this->connection->expects($this->once())
            ->method('newTable')
            ->with($tableName)
            ->willReturn($table);
        $table->expects($this->any())
            ->method('addColumn')
            ->willReturnMap(
                [
                    ['entity_id', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false], 'Entity ID'],
                    ['field', Table::TYPE_INTEGER, null]
                ]
            );
        $this->connection->expects($this->once())
            ->method('createTable')
            ->with($table);
        $this->resource->expects($this->once())
            ->method('getIdxName')
            ->with($tableName, ['field'], AdapterInterface::INDEX_TYPE_FULLTEXT)
            ->willReturn($idxName);
        $table->expects($this->once())
            ->method('addIndex')
            ->with($idxName, ['field'], ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]);
        $this->object->create($index, $fields);
    }
}
