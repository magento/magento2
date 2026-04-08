<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 *
 * @noinspection PhpDeprecationInspection
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\DB\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SqlVersionProviderTest
 *
 * Tests SqlVersionProvider
 */
class SqlVersionProviderTest extends TestCase
{
    /**
     * @var SqlVersionProvider
     */
    private $sqlVersionProvider;

    /**
     * @var MockBuilder|ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MockObject|Mysql
     */
    private $mysqlAdapter;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var array
     */
    private $supportedVersionPatterns = [
        'MySQL-8' => '^8\.0\.',
        'MySQL-8.4' => '^8\.4\.',
        'MySQL-5.7' => '^5\.7\.',
        'MariaDB-(10.2-10.6)' => '^10\.[2-6]\.',
        'MariaDB-11.4' => '^11\.4\.'
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->resourceConnection = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection']
        );
        $this->mysqlAdapter = $this->createPartialMock(
            Mysql::class,
            ['fetchPairs']
        );
        $this->resourceConnection->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->mysqlAdapter);
    }

    /**
     * @param array $versionVariableValue
     * @param string $expectedResult
     *
     * @return void
     * @throws ConnectionException
     */
    #[DataProvider('executeDataProvider')]
    public function testGetSqlVersionProviderReturnsRightResponse(
        array $versionVariableValue,
        string $expectedResult
    ): void {
        $this->prepareSqlProviderAndMySQLAdapter($versionVariableValue);
        $this->assertEquals($expectedResult, $this->sqlVersionProvider->getSqlVersion());
    }

    /**
     * @return void
     */
    public function testSqlVersionProviderThrowsExceptionWhenNonSupportedEngineUsed(): void
    {
        $this->prepareSqlProviderAndMySQLAdapter(['version' => '10.7.0-MariaDB-1:10.7.0+maria~bionic']);
        $this->expectExceptionMessage('Current version of RDBMS is not supported.');
        $this->expectException(ConnectionException::class);
        $this->sqlVersionProvider->getSqlVersion();
    }

    /**
     * @return array
     */
    public static function executeDataProvider(): array
    {
        return [
            'MariaDB-10.6' => [
                ['version' => '10.6.12-MariaDB'],
                '10.6.'
            ],
            'MariaDB-10.4' => [
                ['version' => '10.4.12-MariaDB-1:10.4.12+maria~bionic'],
                '10.4.'
            ],
            'MariaDB-10.2' => [
                ['version' => '10.2.31-MariaDB-1:10.2.31+maria~bionic'],
                '10.2.'
            ],
            'MariaDB-11.4' => [
                ['version' => '11.4.2-MariaDB'],
                SqlVersionProvider::MARIA_DB_11_4_VERSION
            ],
            'MySQL-5.7' => [
                ['version' => '5.7.29'],
                SqlVersionProvider::MYSQL_5_7_VERSION,
            ],
            'MySQL-8' => [
                ['version' => '8.0.19'],
                SqlVersionProvider::MYSQL_8_0_VERSION,
            ],
            'MySQL-8.4' => [
                ['version' => '8.4.0'],
                SqlVersionProvider::MYSQL_8_4_VERSION,
            ],
            'Percona' => [
                ['version' => '5.7.29-32'],
                SqlVersionProvider::MYSQL_5_7_VERSION,
            ],
        ];
    }

    /**
     * @param array $versionVariableValue
     *
     * @return void
     */
    private function prepareSqlProviderAndMySQLAdapter(array $versionVariableValue): void
    {
        $this->mysqlAdapter->expects($this->atLeastOnce())
            ->method('fetchPairs')
            ->willReturn($versionVariableValue);
        $this->sqlVersionProvider = $this->objectManager->getObject(
            SqlVersionProvider::class,
            [
                'resourceConnection' => $this->resourceConnection,
                'supportedVersionPatterns' => $this->supportedVersionPatterns
            ]
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->sqlVersionProvider);
        unset($this->mysqlAdapter);
    }
}
