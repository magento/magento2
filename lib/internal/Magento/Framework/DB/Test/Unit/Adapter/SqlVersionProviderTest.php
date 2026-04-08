<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SqlVersionProviderTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var SqlVersionProvider
     */
    private SqlVersionProvider $sqlVersionProvider;

    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->sqlVersionProvider = new SqlVersionProvider(
            $this->resourceConnection,
            [
                '^8\.0\.',
                '^8\.4\.',
                '^5\.7\.',
                '^10\.(?:[2-6]|11)\.',
                '^11\.4\.'
            ]
        );
    }

    /**     */
    #[DataProvider('mariaDbSuffixKeyDataProvider')]
    public function testGetMariaDbSuffixKey(
        string $sqlExactVersion,
        string $expectedSuffixKey
    ): void {
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->expects($this->exactly(2))->method('fetchPairs')->willReturn(
            ['version' => $sqlExactVersion]
        );
        $this->resourceConnection->expects($this->any())
            ->method('getConnection')
            ->willReturn($adapter);

        $this->assertSame(
            $expectedSuffixKey,
            $this->sqlVersionProvider->getMariaDbSuffixKey()
        );
    }

    /**
     * Covers:
     *  - default fallback
     *  - 10.4.x branch
     *  - 10.6.x branch
     *  - 11.4.x branch mapping to 10.6.11 suffix
     *  - 10.11.x branch
     */
    public static function mariaDbSuffixKeyDataProvider(): array
    {
        return [
            'version below threshold uses default suffix (10.6.11)' => [
                'sqlExactVersion'   => '10.2.44',
                'expectedSuffixKey' => SqlVersionProvider::MARIA_DB_10_6_11_VERSION,
            ],
            'MariaDB 10.4.x at/above 10.4.27 uses 10.4.27 suffix' => [
                'sqlExactVersion'   => '10.4.27',
                'expectedSuffixKey' => SqlVersionProvider::MARIA_DB_10_4_27_VERSION,
            ],
            'MariaDB 10.6.x at/above 10.4.27 uses 10.6.11 suffix' => [
                'sqlExactVersion' => '10.6.11',
                'expectedSuffixKey' => SqlVersionProvider::MARIA_DB_10_6_11_VERSION,
            ],
            'MariaDB 11.4.x at/above threshold maps to 10.6.11 suffix' => [
                'sqlExactVersion' => '11.4.3',
                'expectedSuffixKey' => SqlVersionProvider::MARIA_DB_10_6_11_VERSION,
            ],
            'MariaDB 10.11.x at/above threshold uses 10.11 suffix' => [
                'sqlExactVersion' => '10.11.6',
                'expectedSuffixKey' => SqlVersionProvider::MARIA_DB_10_11_VERSION,
            ],
        ];
    }
}
