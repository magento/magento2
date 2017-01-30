<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Test\Unit;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Framework\Indexer\IndexStructure
 */
class IndexStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexScopeResolver;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flatScopeResolver;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionInterface;

    /**
     * @var \Magento\Framework\Indexer\IndexStructure
     */
    private $target;

    protected function setUp()
    {
        $this->connectionInterface = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionInterface);
        $this->indexScopeResolver = $this->getMockBuilder('\Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver')
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->flatScopeResolver = $this->getMockBuilder('\Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver')
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->target = $objectManager->getObject(
            '\Magento\Framework\Indexer\IndexStructure',
            [
                'resource' => $this->resource,
                'indexScopeResolver' => $this->indexScopeResolver,
                'flatScopeResolver' => $this->flatScopeResolver
            ]
        );
    }

    /**
     * @param string $table
     * @param array $dimensions
     * @param bool $isTableExist
     */
    public function testDelete()
    {
        $index = 'index_name';
        $dimensions = [
            'index_name_scope_3' => $this->createDimensionMock('scope', 3),
            'index_name_scope_5' => $this->createDimensionMock('scope', 5),
            'index_name_scope_1' => $this->createDimensionMock('scope', 1),
        ];
        $expectedTable = 'index_name_scope3_scope5_scope1';
        $this->indexScopeResolver->expects($this->once())
            ->method('resolve')
            ->with($index, $dimensions)
            ->willReturn($expectedTable);
        $this->flatScopeResolver->expects($this->once())
            ->method('resolve')
            ->with($index, $dimensions)
            ->willReturn($index . '_flat');
        $position = 0;
        $position = $this->mockDropTable($position, $expectedTable, true);
        $this->mockDropTable($position, $index . '_flat', true);

        $this->target->delete($index, $dimensions);
    }

    public function testCreateWithEmptyFields()
    {
        $fields = [
            [
                'name' => 'fieldName1',
                'type' => 'fieldType1',
                'size' => 'fieldSize1',
            ],
            [
                'name' => 'fieldName2',
                'type' => 'fieldType2',
                'size' => 'fieldSize2',
            ],
            [
                'name' => 'fieldName3',
                'type' => 'fieldType3',
                'size' => 'fieldSize3',
            ],
            [
                'name' => 'fieldName3',
                'dataType' => 'varchar',
                'type' => 'text',
                'size' => '255',
            ],
            [
                'name' => 'fieldName3',
                'dataType' => 'mediumtext',
                'type' => 'text',
                'size' => '16777216',
            ],
            [
                'name' => 'fieldName3',
                'dataType' => 'text',
                'type' => 'text',
                'size' => '65536',
            ]
        ];
        $index = 'index_name';
        $expectedTable = 'index_name_scope3_scope5_scope1';
        $dimensions = [
            'index_name_scope_3' => $this->createDimensionMock('scope', 3),
            'index_name_scope_5' => $this->createDimensionMock('scope', 5),
            'index_name_scope_1' => $this->createDimensionMock('scope', 1),
        ];
        $position = 0;
        $this->indexScopeResolver->expects($this->once())
            ->method('resolve')
            ->with($index, $dimensions)
            ->willReturn($expectedTable);
        $this->flatScopeResolver->expects($this->once())
            ->method('resolve')
            ->with($index, $dimensions)
            ->willReturn($index . '_flat');
        $position = $this->mockFulltextTable($position, $expectedTable, true);
        $this->mockFlatTable($position, $index . '_flat');

        $this->target->create($index, $fields, $dimensions);
    }

    /**
     * @param string $name
     * @param string $value
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createDimensionMock($name, $value)
    {
        $dimension = $this->getMockBuilder('\Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getName', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($value);
        return $dimension;
    }

    private function mockDropTable($callNumber, $tableName, $isTableExist)
    {
        $this->connectionInterface->expects($this->at($callNumber++))
            ->method('isTableExists')
            ->with($tableName)
            ->willReturn($isTableExist);
        if ($isTableExist) {
            $this->connectionInterface->expects($this->at($callNumber++))
                ->method('dropTable')
                ->with($tableName)
                ->willReturn(true);
        }
        return $callNumber;
    }

    private function mockFlatTable($callNumber, $tableName)
    {
        $table = $this->getMockBuilder('\Magento\Framework\DB\Ddl\Table')
            ->setMethods(['addColumn', 'getColumns'])
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->any())
            ->method('addColumn')
            ->willReturnSelf();

        $this->connectionInterface->expects($this->at($callNumber++))
            ->method('newTable')
            ->with($tableName)
            ->willReturn($table);
        $this->connectionInterface->expects($this->at($callNumber++))
            ->method('createTable')
            ->with($table)
            ->willReturnSelf();

        return $callNumber;
    }

    private function mockFulltextTable($callNumber, $tableName)
    {
        $table = $this->getMockBuilder('\Magento\Framework\DB\Ddl\Table')
            ->setMethods(['addColumn', 'addIndex'])
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->at(0))
            ->method('addColumn')
            ->with(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false],
                'Entity ID'
            )->willReturnSelf();
        $table->expects($this->at(1))
            ->method('addColumn')
            ->with(
                'attribute_id',
                Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => true]
            )->willReturnSelf();

        $table->expects($this->at(2))
            ->method('addColumn')
            ->with(
                'data_index',
                Table::TYPE_TEXT,
                '4g',
                ['nullable' => true],
                'Data index'
            )->willReturnSelf();

        $table->expects($this->at(3))
            ->method('addIndex')
            ->with(
                'idx_primary',
                ['entity_id', 'attribute_id'],
                ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
            )->willReturnSelf();
        $table->expects($this->at(4))
            ->method('addIndex')
            ->with(
                'FTI_FULLTEXT_DATA_INDEX',
                ['data_index'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )->willReturnSelf();

        $this->connectionInterface->expects($this->at($callNumber++))
            ->method('newTable')
            ->with($tableName)
            ->willReturn($table);
        $this->connectionInterface->expects($this->at($callNumber++))
            ->method('createTable')
            ->with($table)
            ->willReturnSelf();

        return $callNumber;
    }
}
