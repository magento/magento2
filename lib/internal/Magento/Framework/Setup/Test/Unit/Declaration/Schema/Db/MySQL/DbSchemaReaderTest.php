<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Helper;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DbSchemaReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;

class DbSchemaReaderTest extends TestCase
{
    private const TEST_DB_NAME = 'test';

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var DefinitionAggregator|MockObject
     */
    private $definitionAggregatorMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var Mysql|MockObject
     */
    private $connectionMock;

    /**
     * @var Helper|MockObject
     */
    private $dbHelperMock;

    /**
     * Set up main mocks
     */
    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getSchemaName')
            ->willReturn(self::TEST_DB_NAME);

        $this->selectMock = $this->createMock(Select::class);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->definitionAggregatorMock = $this->createMock(DefinitionAggregator::class);
        $this->dbHelperMock = $this->createMock(Helper::class);
    }

    /**
     * Test for read tables for cases with one or multiple installations into the same database
     *
     * @param array $nonPrefixedTables
     * @param array $prefixedTables
     * @param string $prefix
     * @param string $escapedPrefix
     * @param array $expectedTables
     * @dataProvider readTablesDataProvider
     */
    public function testReadTables(
        array $nonPrefixedTables,
        array $prefixedTables,
        string $prefix,
        string $escapedPrefix,
        array $expectedTables
    ) {
        $this->prepareSchemaMocks($nonPrefixedTables, $prefixedTables, $prefix, $escapedPrefix);
        $dbSchemaReader = new DbSchemaReader(
            $this->resourceConnectionMock,
            $this->definitionAggregatorMock,
            $this->dbHelperMock
        );
        $actualTables = $dbSchemaReader->readTables('default');

        $this->assertEquals($expectedTables, $actualTables);
    }

    /**
     * Prepare schema mocks
     *
     * @param array $nonPrefixedTables
     * @param array $prefixedTables
     * @param string $prefix
     * @param string $escapedPrefix
     */
    private function prepareSchemaMocks(
        array $nonPrefixedTables,
        array $prefixedTables,
        string $prefix,
        string $escapedPrefix
    ) {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTablePrefix')
            ->willReturn($prefix);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with(
                ['information_schema.TABLES'],
                ['TABLE_NAME']
            )->willReturnSelf();

        $whereExecuteCount = 2;
        $whereRules = [
            ['TABLE_SCHEMA = ?', self::TEST_DB_NAME],
            ['TABLE_TYPE = ?', DbSchemaReader::MYSQL_TABLE_TYPE]
        ];
        if ($prefix) {
            $this->dbHelperMock->expects($this->once())
                ->method('addLikeEscape')
                ->with(
                    $prefix,
                    ['position' => 'start']
                )
                ->willReturn($escapedPrefix);

            $whereExecuteCount++;
            $whereRules[] = ['TABLE_NAME LIKE ?', $escapedPrefix];
        }
        $this->selectMock->expects($this->exactly($whereExecuteCount))
            ->method('where')
            ->withConsecutive(...$whereRules)
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock)
            ->willReturn(empty($prefix) ? array_merge($nonPrefixedTables, $prefixedTables) : $prefixedTables);
    }

    /**
     * Read tables tests data provider
     *
     * @return array[]
     */
    public function readTablesDataProvider(): array
    {
        return [
            [
                ['first_table', 'second_table'],
                [],
                '',
                '',
                ['first_table', 'second_table']
            ],
            [
                ['first_table', 'second_table'],
                ['prefix_first_table', 'prefix_second_table'],
                '',
                '',
                ['first_table', 'second_table', 'prefix_first_table', 'prefix_second_table']
            ],
            [
                ['first_table', 'second_table'],
                ['prefix_first_table', 'prefix_second_table'],
                'prefix_',
                'prefix\\_%',
                ['prefix_first_table', 'prefix_second_table']
            ]
        ];
    }
}
