<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Module\Setup;

class SetupTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceModelMock;

    protected function setUp()
    {
        $this->resourceModelMock = $this->createMock(ResourceConnection::class);
        $this->connection = $this->getMockForAbstractClass(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->resourceModelMock->expects($this->any())
            ->method('getConnection')
            ->with(self::CONNECTION_NAME)
            ->will($this->returnValue($this->connection));
        $this->resourceModelMock->expects($this->any())
            ->method('getConnectionByName')
            ->with(ResourceConnection::DEFAULT_CONNECTION)
            ->willReturn($this->connection);
        $this->setup = new Setup($this->resourceModelMock, self::CONNECTION_NAME);
    }

    public function testGetIdxName()
    {
        $tableName = 'table';
        $fields = ['field'];
        $indexType = 'index_type';
        $expectedIdxName = 'idxName';

        $this->resourceModelMock->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->will($this->returnValue($tableName));

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

        $this->resourceModelMock->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->will($this->returnValue($tableName));

        $this->connection->expects($this->once())
            ->method('getForeignKeyName')
            ->with($tableName, $columnName, $refTable, $refColumnName)
            ->will($this->returnValue('fkName'));

        $this->assertEquals('fkName', $this->setup->getFkName($tableName, $columnName, $refTable, $refColumnName));
    }
}
