<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\FlagManager;
use Magento\MediaGallerySynchronizationApi\Model\FetchBatchesInterface;
use Psr\Log\LoggerInterface;

/**
 * Select data from database by provided batch size
 */
class FetchBatches implements FetchBatchesInterface
{
    private const LAST_EXECUTION_TIME_CODE = 'media_content_last_execution';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @param FlagManager $flagManager
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param int $pageSize
     */
    public function __construct(
        FlagManager $flagManager,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        int $pageSize
    ) {
        $this->flagManager = $flagManager;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->pageSize = $pageSize;
    }

    /**
     * Get data from table by batches, based on limit offset value.
     *
     * @param string $tableName
     * @param array $columns
     * @param string|null $dateColumnName
     */
    public function execute(string $tableName, array $columns, ?string $dateColumnName = null): \Traversable
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName($tableName);
            $totalPages = $this->getTotalPages($tableName);

            for ($page = 0; $page < $totalPages; $page++) {
                $offset = $page * $this->pageSize;
                $select = $connection->select()
                    ->from($this->resourceConnection->getTableName($tableName), $columns)
                    ->limit($this->pageSize, $offset);
                if (!empty($dateColumnName)) {
                    $select = $this->addLastExecutionCondition($select, $dateColumnName);
                }
                yield $connection->fetchAll($select);
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(
                __(
                    'Could not fetch data from %tableName',
                    [
                        'tableName' => $tableName
                    ]
                )
            );
        }
    }

    /**
     * Get where condition if last execution time set
     *
     * @param Select $select
     * @param string $dateColumnName
     * @return Select
     */
    private function addLastExecutionCondition(Select $select, string $dateColumnName): Select
    {
        $lastExecutionTime = $this->flagManager->getFlagData(self::LAST_EXECUTION_TIME_CODE);
        if (!empty($lastExecutionTime)) {
            return $select->where($dateColumnName . ' > ?', $lastExecutionTime);
        }
        return $select;
    }

    /**
     * Return number of total pages by page size
     *
     * @param string $tableName
     * @return float
     */
    private function getTotalPages(string $tableName): float
    {
        $connection = $this->resourceConnection->getConnection();
        $total =  $connection->fetchOne($connection->select()->from($tableName, 'COUNT(*)'));
        return ceil($total / $this->pageSize);
    }
}
