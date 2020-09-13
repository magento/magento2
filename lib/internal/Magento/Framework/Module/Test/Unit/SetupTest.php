<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Module\Setup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetupTest extends TestCase
{
    const CONNECTION_NAME = 'connection';

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceModel;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var Setup
     */
    private $object;

    protected function setUp(): void
    {
        $this->resourceModel = $this->createMock(ResourceConnection::class);
        $this->connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceModel->expects($this->any())
            ->method('getConnection')
            ->with(self::CONNECTION_NAME)
            ->willReturn($this->connection);
        $this->resourceModel->expects($this->any())
            ->method('getConnectionByName')
            ->with(ResourceConnection::DEFAULT_CONNECTION)
            ->willReturn($this->connection);
        $this->object = new Setup($this->resourceModel, self::CONNECTION_NAME);
    }

    public function testGetConnection()
    {
        $this->assertSame($this->connection, $this->object->getConnection());
        // Check that new connection is not created every time
        $this->assertSame($this->connection, $this->object->getConnection());
    }

    public function testSetTableName()
    {
        $tableName = 'table';
        $expectedTableName = 'expected_table';

        $this->assertEmpty($this->object->getTable($tableName));
        $this->object->setTable($tableName, $expectedTableName);
        $this->assertSame($expectedTableName, $this->object->getTable($tableName));
    }

    public function testGetTable()
    {
        $tableName = 'table';
        $expectedTableName = 'expected_table';

        $this->resourceModel->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->willReturn($expectedTableName);

        $this->assertSame($expectedTableName, $this->object->getTable($tableName));
        // Check that table name is cached
        $this->assertSame($expectedTableName, $this->object->getTable($tableName));
    }

    public function testTableExists()
    {
        $tableName = 'table';
        $this->object->setTable($tableName, $tableName);
        $this->connection->expects($this->once())
            ->method('isTableExists')
            ->with($tableName)
            ->willReturn(true);
        $this->assertTrue($this->object->tableExists($tableName));
    }

    public function testRun()
    {
        $q = 'SELECT something';
        $this->connection->expects($this->once())
            ->method('query')
            ->with($q);
        $this->object->run($q);
    }

    public function testStartSetup()
    {
        $this->connection->expects($this->once())
            ->method('startSetup');
        $this->object->startSetup();
    }

    public function testEndSetup()
    {
        $this->connection->expects($this->once())
            ->method('endSetup');
        $this->object->endSetup();
    }
}
