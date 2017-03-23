<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    protected function setUp()
    {
        $this->connectionFactory = $this->getMock(\Magento\Setup\Module\ConnectionFactory::class, [], [], '', false);
        $this->connection = $this->getMockForAbstractClass(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->connectionFactory->expects($this->any())->method('create')->willReturn($this->connection);
        $this->dbValidator = new DbValidator($this->connectionFactory);
    }

    public function testCheckDatabaseConnection()
    {
        $this->connection
            ->expects($this->exactly(2))
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.6.0-0ubuntu0.12.04.1');
        $pdo = $this->getMockForAbstractClass(\Zend_Db_Statement_Interface::class, [], '', false);
        $this->connection
            ->expects($this->atLeastOnce())
            ->method('query')
            ->willReturn($pdo);

        $listOfPrivileges = [
            ['SELECT'],
            ['INSERT'],
            ['UPDATE'],
            ['DELETE'],
            ['CREATE'],
            ['DROP'],
            ['REFERENCES'],
            ['INDEX'],
            ['ALTER'],
            ['CREATE TEMPORARY TABLES'],
            ['LOCK TABLES'],
            ['EXECUTE'],
            ['CREATE VIEW'],
            ['SHOW VIEW'],
            ['CREATE ROUTINE'],
            ['ALTER ROUTINE'],
            ['EVENT'],
            ['TRIGGER'],
        ];
        $accessibleDbs = ['some_db', 'name', 'another_db'];

        $pdo->expects($this->atLeastOnce())
            ->method('fetchAll')
            ->willReturnMap(
                [
                    [\PDO::FETCH_COLUMN, 0, $accessibleDbs],
                    [\PDO::FETCH_NUM, null, $listOfPrivileges]
                ]
            );
        $this->assertEquals(true, $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password'));
        $this->assertEquals(true, $this->dbValidator->checkDatabaseConnection('name', 'host:3339', 'user', 'password'));
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessage Database user does not have enough privileges.
     */
    public function testCheckDatabaseConnectionNotEnoughPrivileges()
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.6.0-0ubuntu0.12.04.1');
        $pdo = $this->getMockForAbstractClass(\Zend_Db_Statement_Interface::class, [], '', false);
        $this->connection
            ->expects($this->atLeastOnce())
            ->method('query')
            ->willReturn($pdo);
        $listOfPrivileges = [['SELECT']];
        $accessibleDbs = ['some_db', 'name', 'another_db'];

        $pdo->expects($this->atLeastOnce())
            ->method('fetchAll')
            ->willReturnMap(
                [
                    [\PDO::FETCH_COLUMN, 0, $accessibleDbs],
                    [\PDO::FETCH_NUM, null, $listOfPrivileges]
                ]
            );
        $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password');
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessage Database 'name' does not exist or specified database server user does not have
     */
    public function testCheckDatabaseConnectionDbNotAccessible()
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.6.0-0ubuntu0.12.04.1');
        $pdo = $this->getMockForAbstractClass(\Zend_Db_Statement_Interface::class, [], '', false);
        $this->connection
            ->expects($this->atLeastOnce())
            ->method('query')
            ->willReturn($pdo);
        $accessibleDbs = ['some_db', 'another_db'];

        $pdo->expects($this->atLeastOnce())
            ->method('fetchAll')
            ->willReturn($accessibleDbs);
        $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password');
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Table prefix length can't be more than
     */
    public function testCheckDatabaseTablePrefixWrongLength()
    {
        $this->assertEquals(
            true,
            $this->dbValidator->checkDatabaseTablePrefix('mvbXzXzItSIr0wrZW3gqgV2UKrWiK1Mj7bkBlW72rZW3gqgV2UKrWiK1M')
        );
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessage Database connection failure.
     */
    public function testCheckDatabaseConnectionFailed()
    {
        $connectionFactory = $this->getMock(\Magento\Setup\Module\ConnectionFactory::class, [], [], '', false);
        $connectionFactory->expects($this->once())->method('create')->willReturn(false);
        $this->dbValidator = new DbValidator($connectionFactory);
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
}
