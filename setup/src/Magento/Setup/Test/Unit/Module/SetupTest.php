<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module;

use \Magento\Setup\Module\Setup;

class SetupTest extends \PHPUnit_Framework_TestCase
{
    const CONNECTION_NAME = 'connection';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var Setup
     */
    private $setup;

    protected function setUp()
    {
        $resourceModel = $this->getMock('\Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->connection = $this->getMockForAbstractClass('\Magento\Framework\DB\Adapter\AdapterInterface');
        $resourceModel->expects($this->any())
            ->method('getConnection')
            ->with(self::CONNECTION_NAME)
            ->will($this->returnValue($this->connection));
        $this->setup = new Setup($resourceModel, self::CONNECTION_NAME);
    }

    public function testGetIdxName()
    {
        $tableName = 'table';
        $fields = ['field'];
        $indexType = 'index_type';
        $expectedIdxName = 'idxName';

        $this->connection->expects($this->once())
            ->method('getIndexName')
            ->with($tableName, $fields, $indexType)
            ->will($this->returnValue($expectedIdxName));

        $this->assertEquals('idxName', $this->setup->getIdxName($tableName, $fields, $indexType));
    }

    public function testGetFkName()
    {
        $tableName = 'table';
        $refTable = 'ref_table';
        $columnName = 'columnName';
        $refColumnName = 'refColumnName';

        $this->connection->expects($this->once())
            ->method('getForeignKeyName')
            ->with($tableName, $columnName, $refTable, $refColumnName)
            ->will($this->returnValue('fkName'));

        $this->assertEquals('fkName', $this->setup->getFkName($tableName, $columnName, $refTable, $refColumnName));
    }
}
