<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Indexer\Model\IndexStructure
 */
class IndexStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;
    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Indexer\Model\IndexStructure
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
            ->with('write')
            ->willReturn($this->adapter);

        $objectManager = new ObjectManager($this);

        $this->target = $objectManager->getObject(
            '\Magento\Indexer\Model\IndexStructure',
            [
                'resource' => $this->resource,
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
        $tableName = 'index_table_name';
        $dimensions = [3,5,1];
        $position = 0;
        foreach ($dimensions as $dimension) {
            $position = $this->mockDropTable($position, $tableName . $dimension, true);
            $position = $this->mockDropTable($position, $tableName . $dimension . '_fulltext', true);
        }

        $this->target->delete($tableName, $dimensions);
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
            ]
        ];
        $table = 'index_table_name';
        $dimensions = [3,5,1];
        $position = 0;
        foreach ($dimensions as $dimension) {
            $tableName = $table . $dimension;
            $fulltextTableName = $tableName . '_fulltext';
            $position = $this->mockFulltextTable($position, $fulltextTableName, true);
            $position = $this->mockFlatTable($position, $tableName, $fields);
        }

        $this->target->create($table, $fields, $dimensions);
    }

    private function mockFlatTable($callNumber, $tableName, array $fields)
    {
        $table = $this->getMockBuilder('\Magento\Framework\DB\Ddl\Table')
            ->setMethods(['addColumn'])
            ->disableOriginalConstructor()
            ->getMock();
        $at = 0;
        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $size = $field['size'];
            $table->expects($this->at($at++))
                ->method('addColumn')
                ->with($name, $type, $size)
                ->willReturnSelf();
        }

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
                'Product ID'
            )->willReturnSelf();
        $table->expects($this->at(0))
            ->method('addColumn')
            ->with(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false],
                'Product ID'
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
