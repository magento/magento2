<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for SqlVersionProvider
 */
class SqlVersionProviderTest extends TestCase
{
    /**
     * @var SqlVersionProvider
     */
    private $sqlVersionProvider;

    /**
     * @var MockObject|ResourceConnection
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
        'MySQL-(5.6,5.7)' => '^5\.[67]\.',
        'MariaDB-(10.0-10.2)' => '^10\.[0-2]\.',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mysqlAdapter = $this->getMockBuilder(Mysql::class)
            ->setMethods(['fetchPairs'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnection
            ->method('getConnection')
            ->willReturn($this->mysqlAdapter);
    }

    /**
     * @dataProvider executeDataProvider
     *
     * @param array $versionVariableValue
     * @param string $expectedResult
     *
     * @return void
     * @throws ConnectionException
     */
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
        $this->prepareSqlProviderAndMySQLAdapter(['version' => '10.5.0-MariaDB-1:10.5.0+maria~bionic']);
        $this->expectExceptionMessage('Current version of RDBMS is not supported.');
        $this->expectException(ConnectionException::class);
        $this->sqlVersionProvider->getSqlVersion();
    }

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            'MariaDB-10.1' => [
                ['version' => '10.1.45-MariaDB-1~bionic'],
                '10.1.'
            ],
            'MariaDB-10.2' => [
                ['version' => '10.2.31-MariaDB-1:10.2.31+maria~bionic'],
                '10.2.'
            ],
            'MySQL-5.6' => [
                ['version' => '5.6.10'],
                '5.6.',
            ],
            'MySQL-5.7' => [
                ['version' => '5.7.29'],
                '5.7.',
            ],
            'Percona' => [
                ['version' => '5.7.29-32'],
                '5.7.',
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
}
