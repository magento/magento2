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
            ->willReturnCallback(
                function ($arg) use ($expectedTable, $index) {
                    if ($arg == $expectedTable) {
                        return true;
                    } elseif ($arg == $index . '_flat') {
                        return true;
                    }
                }
            );
        $this->connectionInterface
            ->method('dropTable')
            ->willReturnCallback(
                function ($arg) use ($expectedTable, $index) {
                    if ($arg == $expectedTable) {
                        return true;
                    } elseif ($arg == $index . '_flat') {
                        return true;
                    }
                }
            );

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
            ->willReturnCallback(
                function ($arg) use ($expectedTable, $index, $table, $table2) {
                    if ($arg == $expectedTable) {
                        return $table;
                    } elseif ($arg == $index . '_flat') {
                        return $table2;
                    }
                }
            );

        $this->connectionInterface
            ->method('createTable')
            ->willReturnCallback(
                function ($arg) use ($table, $table2) {
                    if ($arg == $table || $arg == $table2) {
                        return $this->connectionInterface;
                    }
                }
            );

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
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3, $arg4, $arg5) use ($table) {
                    if ($arg1 == 'entity_id' &&
                        $arg2 == Table::TYPE_INTEGER &&
                        $arg3 == 10 && $arg4 == ['unsigned' => true, 'nullable' => false] &&
                        $arg5 == 'Entity ID') {
                        return $table;
                    } elseif ($arg1 == 'attribute_id' &&
                        $arg2 == Table::TYPE_TEXT &&
                        $arg3 == 255 &&
                        $arg4 == ['unsigned' => true, 'nullable' => true]) {
                        return $table;
                    } elseif ($arg1 == 'data_index' &&
                        $arg2 == Table::TYPE_TEXT &&
                        $arg3 == '4g' &&
                        $arg4 == ['nullable' => true] &&
                        $arg5 == 'Data index') {
                        return $table;
                    }
                }
            );
        $table
            ->method('addIndex')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($table) {
                    if ($arg1 == 'idx_primary' &&
                        $arg2 == ['entity_id', 'attribute_id'] &&
                        $arg3 == ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]) {
                        return $table;
                    } elseif ($arg1 == 'FTI_FULLTEXT_DATA_INDEX' &&
                        $arg2 == ['data_index'] &&
                        $arg3 == ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]) {
                        return $table;
                    }
                }
            );

        return $table;
    }
}
