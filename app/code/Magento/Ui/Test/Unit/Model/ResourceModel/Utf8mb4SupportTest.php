<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Ui\Model\ResourceModel\Utf8mb4Support;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class Utf8mb4SupportTest extends TestCase
{
    /**
     * @var ResourceConnection&MockObject
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var AdapterInterface&MockObject
     */
    private AdapterInterface $connection;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var Utf8mb4Support
     */
    private Utf8mb4Support $model;

    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')->willReturnCallback(
            static fn(string $tableName): string => $tableName
        );

        $this->model = new Utf8mb4Support($this->resourceConnection, $this->logger);
    }

    public function testReturnsTrueForUtf8mb4ColumnSupport(): void
    {
        $this->connection->method('quote')->willReturnCallback(
            static fn(string $value): string => "'" . $value . "'"
        );
        $this->connection->method('fetchRow')->willReturnCallback(
            static function (string $query): array {
                if (str_contains($query, '@@character_set_connection')) {
                    return [
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_general_ci',
                    ];
                }

                return [
                    'Collation' => 'utf8mb4_general_ci',
                ];
            }
        );

        self::assertTrue($this->model->isColumnSupported('catalog_product_entity_text', 'value'));
    }

    public function testReturnsFalseForLegacyConnection(): void
    {
        $this->connection->method('fetchRow')->willReturnMap([
            [
                'SELECT @@character_set_connection AS charset, @@collation_connection AS collation',
                [],
                ['charset' => 'utf8', 'collation' => 'utf8_general_ci'],
            ],
        ]);

        self::assertFalse($this->model->isColumnSupported('cms_page', 'content'));
    }

    public function testCachesColumnSupportByTarget(): void
    {
        $this->connection->expects(self::exactly(2))
            ->method('fetchRow')
            ->willReturnMap([
                [
                    'SELECT @@character_set_connection AS charset, @@collation_connection AS collation',
                    [],
                    ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci'],
                ],
                [
                    'SHOW FULL COLUMNS FROM `cms_page` LIKE \'content\'',
                    [],
                    ['Collation' => 'utf8mb4_general_ci'],
                ],
            ]);
        $this->connection->method('quote')->willReturnCallback(
            static fn(string $value): string => "'" . $value . "'"
        );

        self::assertTrue($this->model->isColumnSupported('cms_page', 'content'));
        self::assertTrue($this->model->isColumnSupported('cms_page', 'content'));
    }

    public function testReturnsFalseWhenColumnLookupFails(): void
    {
        $this->logger->expects(self::once())->method('warning');
        $this->connection->method('fetchRow')->willThrowException(new \RuntimeException('Lookup failed'));

        self::assertFalse($this->model->isColumnSupported('cms_page', 'content'));
    }
}
