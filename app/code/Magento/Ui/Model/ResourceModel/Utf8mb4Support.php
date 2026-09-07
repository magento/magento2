<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

class Utf8mb4Support implements Utf8mb4SupportInterface
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var array<string, bool>
     */
    private array $columnSupportCache = [];

    /**
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function isColumnSupported(string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;

        if (array_key_exists($cacheKey, $this->columnSupportCache)) {
            return $this->columnSupportCache[$cacheKey];
        }

        try {
            $connection = $this->resourceConnection->getConnection();

            $this->columnSupportCache[$cacheKey] = $this->isConnectionUtf8mb4($connection)
                && $this->isColumnCharsetUtf8mb4($connection, $table, $column);
        } catch (\Throwable $exception) {
            $this->logger->warning($exception->getMessage());
            $this->columnSupportCache[$cacheKey] = false;
        }

        return $this->columnSupportCache[$cacheKey];
    }

    /**
     * Check if connection is set to utf8mb4.
     *
     * @param AdapterInterface $connection
     * @return bool
     */
    private function isConnectionUtf8mb4(AdapterInterface $connection): bool
    {
        $row = $connection->fetchRow(
            'SELECT @@character_set_connection AS charset, @@collation_connection AS collation'
        );

        return is_array($row)
            && isset($row['charset'], $row['collation'])
            && str_starts_with((string)$row['charset'], 'utf8mb4')
            && str_starts_with((string)$row['collation'], 'utf8mb4');
    }

    /**
     * Check if column to be updated supports utf8mb4 character set.
     *
     * @param AdapterInterface $connection
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function isColumnCharsetUtf8mb4(AdapterInterface $connection, string $table, string $column): bool
    {
        $row = $connection->fetchRow(
            sprintf(
                'SHOW FULL COLUMNS FROM `%s` LIKE %s',
                $this->resourceConnection->getTableName($table),
                $connection->quote($column)
            )
        );

        // Text/blob columns without collation are not safe for utf8mb4 round-tripping.
        return is_array($row)
            && !empty($row['Collation'])
            && str_starts_with((string)$row['Collation'], 'utf8mb4');
    }
}
