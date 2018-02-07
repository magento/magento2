<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for an environment-dependent DB adapter that implements \Magento\Framework\DB\Adapter\AdapterInterface
 */
namespace Magento\Framework\DB\Adapter;

class InterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var string
     */
    protected $_tableName;

    /**
     * @var string
     */
    protected $_oneColumnIdxName;

    /**
     * @var string
     */
    protected $_twoColumnIdxName;

    protected function setUp()
    {
        /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $installer */
        $installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Framework\Setup\ModuleDataSetupInterface'
        );
        $this->_connection = $installer->getConnection();
        $this->_tableName = $this->_connection->getTableName('table_two_column_idx');
        $this->_oneColumnIdxName = $this->_connection->getIndexName($this->_tableName, ['column1']);
        $this->_twoColumnIdxName = $this->_connection->getIndexName($this->_tableName, ['column1', 'column2']);

        $table = $this->_connection->newTable(
            $this->_tableName
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'column1',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
        )->addColumn(
            'column2',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
        )->addIndex(
            $this->_oneColumnIdxName,
            ['column1']
        )->addIndex(
            $this->_twoColumnIdxName,
            ['column1', 'column2']
        );
        $this->_connection->createTable($table);
    }

    /**
     * Cleanup DDL cache for the fixture table
     */
    protected function tearDown()
    {
        $this->_connection->dropTable($this->_tableName);
        $this->_connection->resetDdlCache($this->_tableName);
        $this->_connection = null;
    }

    protected function assertPreConditions()
    {
        $this->assertTrue(
            $this->_connection->tableColumnExists($this->_tableName, 'column1'),
            'Table column "column1" must be provided by the fixture.'
        );
        $this->assertTrue(
            $this->_connection->tableColumnExists($this->_tableName, 'column2'),
            'Table column "column2" must be provided by the fixture.'
        );
        $this->assertEquals(
            ['column1'],
            $this->_getIndexColumns($this->_tableName, $this->_oneColumnIdxName),
            'Single-column index must be provided by the fixture.'
        );
        $this->assertEquals(
            ['column1', 'column2'],
            $this->_getIndexColumns($this->_tableName, $this->_twoColumnIdxName),
            'Multiple-column index must be provided by the fixture.'
        );
    }

    /**
     * Retrieve list of columns used for an index or return false, if an index with a given name does not exist
     *
     * @param string $tableName
     * @param string $indexName
     * @param string|null $schemaName
     * @return array|false
     */
    protected function _getIndexColumns($tableName, $indexName, $schemaName = null)
    {
        foreach ($this->_connection->getIndexList($tableName, $schemaName) as $idxData) {
            if ($idxData['KEY_NAME'] == $indexName) {
                return $idxData['COLUMNS_LIST'];
            }
        }
        return false;
    }

    public function testDropColumn()
    {
        $this->_connection->dropColumn($this->_tableName, 'column1');
        $this->assertFalse(
            $this->_connection->tableColumnExists($this->_tableName, 'column1'),
            'Table column must not exist after it has been dropped.'
        );
    }

    /**
     * @depends testDropColumn
     */
    public function testDropColumnRemoveFromIndexes()
    {
        $this->_connection->dropColumn($this->_tableName, 'column1');
        $this->assertFalse(
            $this->_getIndexColumns($this->_tableName, $this->_oneColumnIdxName),
            'Column index must be dropped along with the column.'
        );
        $this->assertEquals(
            ['column2'],
            $this->_getIndexColumns($this->_tableName, $this->_twoColumnIdxName),
            'References to the dropped column must be removed from the multiple-column indexes.'
        );
    }

    /**
     * @depends testDropColumn
     */
    public function testDropColumnRemoveIndexDuplicate()
    {
        $this->_connection->dropColumn($this->_tableName, 'column2');
        $this->assertEquals(
            ['column1'],
            $this->_getIndexColumns($this->_tableName, $this->_oneColumnIdxName),
            'Column index must be preserved.'
        );
        $this->assertFalse(
            $this->_getIndexColumns($this->_tableName, $this->_twoColumnIdxName),
            'Multiple-column index must be dropped to not duplicate existing index by indexed columns.'
        );
    }

    /**
     * @param array $columns
     * @param array $data
     * @param array $expected
     * @dataProvider insertArrayDataProvider
     */
    public function testInsertArray(array $columns, array $data, array $expected)
    {
        $this->_connection->insertArray($this->_tableName, $columns, $data);
        $select = $this->_connection->select()->from($this->_tableName, array_keys($expected[0]))->order('column1');
        $result = $this->_connection->fetchAll($select);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for insertArray() test
     *
     * @return array
     */
    public function insertArrayDataProvider()
    {
        return [
            'one column' => [
                ['column1'],
                [[1], [2]],
                [['column1' => 1, 'column2' => null], ['column1' => 2, 'column2' => null]],
            ],
            'one column simple' => [
                ['column1'],
                [1, 2],
                [['column1' => 1, 'column2' => null], ['column1' => 2, 'column2' => null]],
            ],
            'two columns' => [
                ['column1', 'column2'],
                [[1, 2], [3, 4]],
                [['column1' => 1, 'column2' => 2], ['column1' => 3, 'column2' => 4]],
            ],
            'several columns with identity' => [ // test possibility to insert data with filled identity field
                ['id', 'column1', 'column2'],
                [[1, 0, 0], [2, 1, 1], [3, 2, 2]],
                [
                    ['id' => 1, 'column1' => 0, 'column2' => 0],
                    ['id' => 2, 'column1' => 1, 'column2' => 1],
                    ['id' => 3, 'column1' => 2, 'column2' => 2]
                ],
            ]
        ];
    }

    /**
     * @expectedException \Zend_Db_Exception
     */
    public function testInsertArrayTwoColumnsWithSimpleData()
    {
        $this->_connection->insertArray($this->_tableName, ['column1', 'column2'], [1, 2]);
    }

    /**
     * @dataProvider insertDataProvider
     */
    public function testInsertMultiple($data)
    {
        $this->_connection->insertMultiple($this->_tableName, $data);

        $select = $this->_connection->select()->from($this->_tableName);
        $result = $this->_connection->fetchRow($select);

        $this->assertEquals($data, $result);
    }

    /**
     * @dataProvider insertDataProvider
     */
    public function testInsertOnDuplicate($data)
    {
        $this->_connection->insertOnDuplicate($this->_tableName, $data);

        $select = $this->_connection->select()->from($this->_tableName);
        $result = $this->_connection->fetchRow($select);

        $this->assertEquals($data, $result);
    }

    /**
     * @dataProvider insertDataProvider
     */
    public function testInsertForce($data)
    {
        $this->assertEquals(1, $this->_connection->insertForce($this->_tableName, $data));

        $select = $this->_connection->select()->from($this->_tableName);
        $result = $this->_connection->fetchRow($select);

        $this->assertEquals($data, $result);
    }

    /**
     * Data provider for insert() tests
     *
     * @return array
     */
    public function insertDataProvider()
    {
        return ['column with identity field' => [['id' => 1, 'column1' => 10, 'column2' => 20]]];
    }
}
