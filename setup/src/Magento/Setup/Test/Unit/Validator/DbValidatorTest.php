<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Validator;

use Magento\Setup\Validator\DbValidator;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;

class DbValidatorTest extends \PHPUnit\Framework\TestCase
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
        $this->connectionFactory = $this->createMock(\Magento\Setup\Module\ConnectionFactory::class);
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
            ['INDEX'],
            ['ALTER'],
            ['CREATE TEMPORARY TABLES'],
            ['LOCK TABLES'],
            ['EXECUTE'],
            ['CREATE VIEW'],
            ['SHOW VIEW'],
            ['CREATE ROUTINE'],
            ['ALTER ROUTINE'],
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
     */
    public function testCheckDatabaseConnectionNotEnoughPrivileges()
    {
        $this->setExpectedException(\Magento\Setup\Exception::class, 'Database user does not have enough privileges.');

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
     */
    public function testCheckDatabaseConnectionDbNotAccessible()
    {
        $this->setExpectedException(\Magento\Setup\Exception::class, 'Database \'name\' does not exist or specified database server user does not have');

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
     */
    public function testCheckDatabaseTablePrefixWrongFormat()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Please correct the table prefix format');

        $this->assertEquals(true, $this->dbValidator->checkDatabaseTablePrefix('_wrong_format'));
    }

    /**
     */
    public function testCheckDatabaseTablePrefixWrongLength()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Table prefix length can\'t be more than');

        $this->assertEquals(
            true,
            $this->dbValidator->checkDatabaseTablePrefix('mvbXzXzItSIr0wrZW3gqgV2UKrWiK1Mj7bkBlW72rZW3gqgV2UKrWiK1M')
        );
    }

    /**
     */
    public function testCheckDatabaseConnectionFailed()
    {
        $this->setExpectedException(\Magento\Setup\Exception::class, 'Database connection failure.');

        $connectionFactory = $this->createMock(\Magento\Setup\Module\ConnectionFactory::class);
        $connectionFactory->expects($this->once())->method('create')->willReturn(false);
        $this->dbValidator = new DbValidator($connectionFactory);
        $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password');
    }

    /**
     */
    public function testCheckDatabaseConnectionIncompatible()
    {
        $this->setExpectedException(\Magento\Setup\Exception::class, 'Sorry, but we support MySQL version');

        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.5.40-0ubuntu0.12.04.1');
        $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password');
    }
}
