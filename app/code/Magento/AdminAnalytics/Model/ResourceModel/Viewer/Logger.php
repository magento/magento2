<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Model\ResourceModel\Viewer;

use Magento\AdminAnalytics\Model\Viewer\Log;
use Magento\AdminAnalytics\Model\Viewer\LogFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Admin Analytics log data logger.
 *
 * Saves and retrieves release notification viewer log data.
 */
class Logger
{
    /**
     * Log table name
     */
    const LOG_TABLE_NAME = 'admin_analytics_usage_version_log';

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @param ResourceConnection $resource
     * @param LogFactory $logFactory
     */
    public function __construct(
        ResourceConnection $resource,
        LogFactory $logFactory
    ) {
        $this->resource = $resource;
        $this->logFactory = $logFactory;
    }

    /**
     * Save (insert new or update existing) log.
     *
     * @param string $lastViewVersion
     * @return bool
     */
    public function log(string $lastViewVersion): bool
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $connection->insertOnDuplicate(
            $this->resource->getTableName(self::LOG_TABLE_NAME),
            [
                'last_viewed_in_version' => $lastViewVersion,
            ],
            [
                'last_viewed_in_version',
            ]
        );
        return true;
    }

    /**
     * Get log by the last view version.
     *
     * @return Log
     */
    public function get(): Log
    {
        return $this->logFactory->create(['data' => $this->loadLatestLogData()]);
    }

    /**
     * Checks is log already exists.
     *
     * @return boolean
     */
    public function checkLogExists(): bool
    {
        $data = $this->logFactory->create(['data' => $this->loadLatestLogData()]);
        $lastViewedVersion = $data->getLastViewVersion();
        return isset($lastViewedVersion);
    }

    /**
     * Load release notification viewer log data by last view version
     *
     * @return array
     */
    private function loadLatestLogData(): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(['log_table' => $this->resource->getTableName(self::LOG_TABLE_NAME)])
            ->order('log_table.id desc')
            ->limit(['count' => 1]);

        $data = $connection->fetchRow($select);
        if (!$data) {
            $data = [];
        }
        return $data;
    }
}
