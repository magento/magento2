<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\Statement;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementAggregator;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementFactory;
use Magento\Framework\Setup\Declaration\Schema\DryRunLogger;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DbSchemaWriter;
use Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Table as DtoFactoriesTable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DbSchemaWriterTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var StatementFactory|MockObject
     */
    private $statementFactory;

    /**
     * @var DryRunLogger|MockObject
     */
    private $dryRunLogger;

    /**
     * @var SqlVersionProvider|MockObject
     */
    private $sqlVersionProvider;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapter;

    /**
     * @var DbSchemaWriter
     */
    private $model;

    /***
     * @var DtoFactoriesTable|MockObject
     */
    private $dtoFactoriesTable;

    protected function setUp(): void
    {
        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statementFactory = $this->getMockBuilder(StatementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dryRunLogger = $this->getMockBuilder(DryRunLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sqlVersionProvider = $this->getMockBuilder(SqlVersionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->resourceConnection->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);
        $this->dtoFactoriesTable = $this->getMockBuilder(DtoFactoriesTable::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new DbSchemaWriter(
            $this->resourceConnection,
            $this->statementFactory,
            $this->dryRunLogger,
            $this->sqlVersionProvider,
            $this->dtoFactoriesTable
        );
    }

    /**
     * Test to check that column modification and adding fk are run as separate queries with MariaDb
     *
     * @param string $dbVersion
     * @param int $numberOfQueries
     * @return void
     *
     * @dataProvider compileDataProvider
     */
    public function testCompileWithColumnModificationAndFK(string $dbVersion, int $numberOfQueries) : void
    {
        $dryRun = false;
        $statementAggregator = $this->getMockBuilder(StatementAggregator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement1 = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement1->expects($this->any())
            ->method('getResource')
            ->willReturn('resource');
        $statement1->expects($this->any())
            ->method('getType')
            ->willReturn('alter');
        $statement1->expects($this->any())
            ->method('getName')
            ->willReturn('column');
        $statement1->expects($this->any())
            ->method('getStatement')
            ->willReturn('MODIFY COLUMN `column1` varchar(64) NOT NULL');

        $statement2 = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement2->expects($this->any())
            ->method('getResource')
            ->willReturn('resource');
        $statement2->expects($this->any())
            ->method('getType')
            ->willReturn('alter');
        $statement2->expects($this->any())
            ->method('getName')
            ->willReturn('FK_COLUMN');
        $statement2->expects($this->any())
            ->method('getStatement')
            ->willReturn('ADD CONSTRAINT `FK_COLUMN` FOREIGN KEY (`column`)');

        $statementBank = [$statement1, $statement2];
        $statementAggregator->expects($this->any())
            ->method('getStatementsBank')
            ->willReturn([$statementBank]);
        $this->sqlVersionProvider->expects($this->any())
            ->method('getSqlVersion')
            ->willReturn($dbVersion);
        $this->adapter->expects($this->exactly($numberOfQueries))
            ->method('query');

        $this->model->compile($statementAggregator, $dryRun);
    }

    /**
     * @return array
     */
    public static function compileDataProvider() : array
    {
        return [
            [SqlVersionProvider::MARIA_DB_10_4_VERSION, 2],
            [SqlVersionProvider::MARIA_DB_10_6_VERSION, 2],
            [SqlVersionProvider::MYSQL_8_0_VERSION, 1],
        ];
    }
}
