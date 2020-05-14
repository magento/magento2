<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Validator;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Setup\Validator\DbValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DbValidatorTest extends TestCase
{
    /**
     * @var DbValidator|MockObject
     */
    private $dbValidator;

    /**
     * @var ConnectionFactory|MockObject
     */
    private $connectionFactory;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connectionFactory = $this->createMock(ConnectionFactory::class);
        $this->connection = $this->getMockForAbstractClass(AdapterInterface::class);
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
        $this->assertTrue($this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password'));
        $this->assertTrue($this->dbValidator->checkDatabaseConnection('name', 'host:3339', 'user', 'password'));
    }

    public function testCheckDatabaseConnectionNotEnoughPrivileges()
    {
        $this->expectException('Magento\Setup\Exception');
        $this->expectExceptionMessage('Database user does not have enough privileges.');
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

    public function testCheckDatabaseConnectionDbNotAccessible()
    {
        $this->expectException('Magento\Setup\Exception');
        $this->expectExceptionMessage(
            'Database \'name\' does not exist or specified database server user does not have'
        );
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
        $this->assertTrue($this->dbValidator->checkDatabaseTablePrefix('test'));
    }

    public function testCheckDatabaseTablePrefixWrongFormat()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Please correct the table prefix format');
        $this->assertTrue($this->dbValidator->checkDatabaseTablePrefix('_wrong_format'));
    }

    public function testCheckDatabaseTablePrefixWrongLength()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Table prefix length can\'t be more than');
        $this->assertTrue(
            $this->dbValidator->checkDatabaseTablePrefix('mvbXzXzItSIr0wrZW3gqgV2UKrWiK1Mj7bkBlW72rZW3gqgV2UKrWiK1M')
        );
    }

    public function testCheckDatabaseConnectionFailed()
    {
        $this->expectException('Magento\Setup\Exception');
        $this->expectExceptionMessage('Database connection failure.');
        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $connectionFactory->expects($this->once())->method('create')->willReturn(false);
        $this->dbValidator = new DbValidator($connectionFactory);
        $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password');
    }

    public function testCheckDatabaseConnectionIncompatible()
    {
        $this->expectException('Magento\Setup\Exception');
        $this->expectExceptionMessage('Sorry, but we support MySQL version');
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.5.40-0ubuntu0.12.04.1');
        $this->dbValidator->checkDatabaseConnection('name', 'host', 'user', 'password');
    }
}
