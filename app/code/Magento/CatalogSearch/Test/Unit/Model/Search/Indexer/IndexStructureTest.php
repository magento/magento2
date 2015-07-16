<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\Indexer;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Indexer\Model\IndexStructure
 */
class IndexStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\ScopeResolver\IndexScopeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexScopeResolver;
    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;
    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexStructure
     */
    private $target;

    protected function setUp()
    {
        $this->adapter = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->atLeastOnce())
            ->method('getConnection')
            ->with(\Magento\Framework\App\Resource::DEFAULT_WRITE_RESOURCE)
            ->willReturn($this->adapter);
        $this->indexScopeResolver = $this->getMockBuilder('\Magento\Indexer\Model\ScopeResolver\IndexScopeResolver')
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->flatScopeResolver = $this->getMockBuilder('\Magento\Indexer\Model\ScopeResolver\FlatScopeResolver')
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->target = $objectManager->getObject(
            \Magento\CatalogSearch\Model\Indexer\IndexStructure::class,
            [
                'resource' => $this->resource,
                'indexScopeResolver' => $this->indexScopeResolver,
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
        $position = 0;
        $position = $this->mockDropTable($position, $expectedTable, true);

        $this->target->delete($index, $dimensions);
    }

    public function testCreateWithEmptyFields()
    {
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
        $position = $this->mockFulltextTable($position, $expectedTable, true);

        $this->target->create($index, $dimensions);
    }

    /**
     * @param string $name
     * @param string $value
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
        $this->adapter->expects($this->at($callNumber++))
            ->method('isTableExists')
            ->with($tableName)
            ->willReturn($isTableExist);
        if ($isTableExist) {
            $this->adapter->expects($this->at($callNumber++))
                ->method('dropTable')
                ->with($tableName)
                ->willReturn(true);
        }
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
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false]
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

        $this->adapter->expects($this->at($callNumber++))
            ->method('newTable')
            ->with($tableName)
            ->willReturn($table);
        $this->adapter->expects($this->at($callNumber++))
            ->method('createTable')
            ->with($table)
            ->willReturnSelf();

        return $callNumber;
    }
}
