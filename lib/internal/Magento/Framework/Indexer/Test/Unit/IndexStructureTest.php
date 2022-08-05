<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\IndexStructure;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\Indexer\IndexStructure
 */
class IndexStructureTest extends TestCase
{
    /**
     * @var IndexScopeResolver|MockObject
     */
    private $indexScopeResolver;

    /**
     * @var FlatScopeResolver|MockObject
     */
    private $flatScopeResolver;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionInterface;

    /**
     * @var IndexStructure
     */
    private $target;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->connectionInterface = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->onlyMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionInterface);
        $this->indexScopeResolver = $this->getMockBuilder(IndexScopeResolver::class)
            ->onlyMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->flatScopeResolver = $this->getMockBuilder(FlatScopeResolver::class)
            ->onlyMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->target = $objectManager->getObject(
            IndexStructure::class,
            [
                'resource' => $this->resource,
                'indexScopeResolver' => $this->indexScopeResolver,
                'flatScopeResolver' => $this->flatScopeResolver
            ]
        );
    }

    /**
     * @return void
     */
    public function testDelete(): void
    {
        $index = 'index_name';
        $dimensions = [
            'index_name_scope_3' => $this->createDimensionMock('scope', 3),
            'index_name_scope_5' => $this->createDimensionMock('scope', 5),
            'index_name_scope_1' => $this->createDimensionMock('scope', 1)
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
        $this->connectionInterface
            ->method('isTableExists')
            ->withConsecutive([$expectedTable], [$index . '_flat'])
            ->willReturn(true, true);
        $this->connectionInterface
            ->method('dropTable')
            ->withConsecutive([$expectedTable], [$index . '_flat'])
            ->willReturnOnConsecutiveCalls(true, true);

        $this->target->delete($index, $dimensions);
    }

    /**
     * @return void
     */
    public function testCreateWithEmptyFields(): void
    {
        $fields = [
            [
                'name' => 'fieldName1',
                'type' => 'fieldType1',
                'size' => 'fieldSize1'
            ],
            [
                'name' => 'fieldName2',
                'type' => 'fieldType2',
                'size' => 'fieldSize2'
            ],
            [
                'name' => 'fieldName3',
                'type' => 'fieldType3',
                'size' => 'fieldSize3'
            ],
            [
                'name' => 'fieldName3',
                'dataType' => 'varchar',
                'type' => 'text',
                'size' => '255'
            ],
            [
                'name' => 'fieldName3',
                'dataType' => 'mediumtext',
                'type' => 'text',
                'size' => '16777216'
            ],
            [
                'name' => 'fieldName3',
                'dataType' => 'text',
                'type' => 'text',
                'size' => '65536'
            ]
        ];
        $index = 'index_name';
        $expectedTable = 'index_name_scope3_scope5_scope1';
        $dimensions = [
            'index_name_scope_3' => $this->createDimensionMock('scope', 3),
            'index_name_scope_5' => $this->createDimensionMock('scope', 5),
            'index_name_scope_1' => $this->createDimensionMock('scope', 1)
        ];
        $this->indexScopeResolver->expects($this->once())
            ->method('resolve')
            ->with($index, $dimensions)
            ->willReturn($expectedTable);
        $this->flatScopeResolver->expects($this->once())
            ->method('resolve')
            ->with($index, $dimensions)
            ->willReturn($index . '_flat');
        $table = $this->mockFulltextTable();
        $table2 = $this->mockFlatTable();

        $this->connectionInterface
            ->method('newTable')
            ->withConsecutive([$expectedTable], [$index . '_flat'])
            ->willReturnOnConsecutiveCalls($table, $table2);
        $this->connectionInterface
            ->method('createTable')
            ->withConsecutive([$table], [$table2])
            ->willReturnSelf();

        $this->target->create($index, $fields, $dimensions);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return MockObject
     */
    private function createDimensionMock($name, $value): MockObject
    {
        $dimension = $this->getMockBuilder(Dimension::class)
            ->onlyMethods(['getName', 'getValue'])
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

    /**
     * @return MockObject
     */
    private function mockFlatTable(): MockObject
    {
        $table = $this->getMockBuilder(Table::class)
            ->onlyMethods(['addColumn', 'getColumns'])
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->any())
            ->method('addColumn')
            ->willReturnSelf();

        return $table;
    }

    /**
     * @return MockObject
     */
    private function mockFulltextTable(): MockObject
    {
        $table = $this->getMockBuilder(Table::class)
            ->onlyMethods(['addColumn', 'addIndex'])
            ->disableOriginalConstructor()
            ->getMock();

        $table
            ->method('addColumn')
            ->withConsecutive(
                [
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Entity ID'
                ],
                [
                    'attribute_id',
                    Table::TYPE_TEXT,
                    255,
                    ['unsigned' => true, 'nullable' => true]
                ],
                [
                    'data_index',
                    Table::TYPE_TEXT,
                    '4g',
                    ['nullable' => true],
                    'Data index'
                ]
            )
            ->willReturnOnConsecutiveCalls($table, $table, $table);
        $table
            ->method('addIndex')
            ->withConsecutive(
                [
                    'idx_primary',
                    ['entity_id', 'attribute_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
                ],
                [
                    'FTI_FULLTEXT_DATA_INDEX',
                    ['data_index'],
                    ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                ]
            )
            ->willReturnOnConsecutiveCalls($table, $table);

        return $table;
    }
}
