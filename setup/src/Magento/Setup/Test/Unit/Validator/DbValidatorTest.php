<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Validator;

use Magento\Setup\Validator\DbValidator;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;

class DbValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DbValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbValidator;

    /**
     * @var ConnectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionFactory;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    private $random;

    protected function setUp()
    {
        $this->connectionFactory = $this->getMock('Magento\Setup\Module\ConnectionFactory', [], [], '', false);
        $this->connection = $this->getMockForAbstractClass('Magento\Framework\DB\Adapter\AdapterInterface');
        $this->connectionFactory->expects($this->any())->method('create')->willReturn($this->connection);
        $this->random = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);
        $this->dbValidator = new DbValidator($this->connectionFactory, $this->random);
    }

    public function testCheckDatabaseConnection()
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.6.0-0ubuntu0.12.04.1');
        $this->assertEquals(true, $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password'));
    }

    public function testCheckDatabaseTablePrefix()
    {
        $this->assertEquals(true, $this->dbValidator->checkDatabaseTablePrefix('test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please correct the table prefix format
     */
    public function testCheckDatabaseTablePrefixWrongFormat()
    {
        $this->assertEquals(true, $this->dbValidator->checkDatabaseTablePrefix('_wrong_format'));
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessage Database connection failure.
     */
    public function testCheckDatabaseConnectionFailed()
    {
        $connectionFactory = $this->getMock('Magento\Setup\Module\ConnectionFactory', [], [], '', false);
        $connectionFactory->expects($this->once())->method('create')->willReturn(false);
        $this->dbValidator = new DbValidator($connectionFactory, $this->random);
        $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password');
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessage Sorry, but we support MySQL version
     */
    public function testCheckDatabaseConnectionIncompatible()
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.5.40-0ubuntu0.12.04.1');
        $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password');
    }

    public function testCheckDatabaseWriteFailedCreateTable()
    {
        $this->random->expects($this->once())->method('getRandomString')->willReturn('new_table');
        $table = $this->getMock('Magento\Framework\DB\Ddl\Table', [], [], '', false);
        $table->expects($this->once())->method('addColumn')->willReturn($table);
        $this->connection->expects($this->once())->method('newTable')->willReturn($table);
        $this->connection->expects($this->once())
            ->method('createTemporaryTable')
            ->willThrowException(new \Zend_Db_Exception());
        $this->connection->expects($this->never())->method('insert');
        $this->connection->expects($this->never())->method('fetchAll');
        $this->assertFalse($this->dbValidator->checkDatabaseWrite('name', 'host', 'user', 'password'));
    }

    public function testCheckDatabaseWriteFailedInsert()
    {
        $this->random->expects($this->once())->method('getRandomString')->willReturn('new_table');
        $table = $this->getMock('Magento\Framework\DB\Ddl\Table', [], [], '', false);
        $table->expects($this->once())->method('addColumn')->willReturn($table);
        $this->connection->expects($this->once())->method('newTable')->willReturn($table);
        $this->connection->expects($this->once())->method('createTemporaryTable');
        $this->connection->expects($this->once())->method('insert');
        $this->connection->expects($this->once())->method('fetchAll')->willReturn([]);
        $this->assertFalse($this->dbValidator->checkDatabaseWrite('name', 'host', 'user', 'password'));
    }

    public function testCheckDatabaseWriteFailedDelete()
    {
        $this->random->expects($this->once())->method('getRandomString')->willReturn('new_table');
        $table = $this->getMock('Magento\Framework\DB\Ddl\Table', [], [], '', false);
        $table->expects($this->once())->method('addColumn')->willReturn($table);
        $this->connection->expects($this->once())->method('newTable')->willReturn($table);
        $this->connection->expects($this->once())->method('createTemporaryTable')->willReturn($table);
        $this->connection->expects($this->once())->method('insert');
        $this->connection->expects($this->once())->method('delete');
        $this->connection->expects($this->exactly(2))
            ->method('fetchAll')
            ->will($this->returnValueMap([
                    ['select * from new_table where testCol = "testing"', [], null, [['testCol' => 'testing']]],
                    ['select * from new_table', [], null, [['testCol' => 'testing']]],
                ]
            ));
        $this->assertFalse($this->dbValidator->checkDatabaseWrite('name', 'host', 'user', 'password'));
    }

    public function testCheckDatabaseWrite()
    {
        $this->random->expects($this->once())->method('getRandomString')->willReturn('new_table');
        $table = $this->getMock('Magento\Framework\DB\Ddl\Table', [], [], '', false);
        $table->expects($this->once())->method('addColumn')->willReturn($table);
        $this->connection->expects($this->once())->method('newTable')->willReturn($table);
        $this->connection->expects($this->once())->method('createTemporaryTable')->willReturn($table);
        $this->connection->expects($this->once())->method('insert');
        $this->connection->expects($this->once())->method('delete');
        $this->connection->expects($this->exactly(2))
            ->method('fetchAll')
            ->will($this->returnValueMap([
                    ['select * from new_table where testCol = "testing"', [], null, [['testCol' => 'testing']]],
                    ['select * from new_table', [], null, []],
                ]
            ));
        $this->assertTrue($this->dbValidator->checkDatabaseWrite('name', 'host', 'user', 'password'));
    }
}
