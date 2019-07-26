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
     * Logger constructor.
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
    public function log(string $lastViewVersion) : bool
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
     * @param string $lastViewVersion
     * @return Log
     */
    public function get(string $lastViewVersion) : Log
    {
        return $this->logFactory->create(['data' => $this->loadLogData($lastViewVersion)]);
    }

    /**
     * Load release notification viewer log data by last view version
     *
     * @param string $lastViewVersion
     * @return array
     */
    private function loadLogData(string $lastViewVersion) : array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName(self::LOG_TABLE_NAME))
            ->where('last_viewed_in_version = ?', $lastViewVersion);

        $data = $connection->fetchRow($select);
        if (!$data) {
            $data = [];
        }
        return $data;
    }
}
