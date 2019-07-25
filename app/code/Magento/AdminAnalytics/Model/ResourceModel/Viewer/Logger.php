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
 * Release notification viewer log data logger.
 *
 * Saves and retrieves release notification viewer log data.
 */
class Logger
{
    /**
     * Log table name
     */
    const LOG_TABLE_NAME = 'admin_usage_viewer_log';

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
     * @param int $viewerId
     * @param string $lastViewVersion
     * @param int $isAdminUsageEnabled
     * @return bool
     */
    public function log(int $viewerId, string $lastViewVersion, int $isAdminUsageEnabled) : bool
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $connection->insertOnDuplicate(
            $this->resource->getTableName(self::LOG_TABLE_NAME),
            [
                'viewer_id' => $viewerId,
                'last_view_version' => $lastViewVersion,
                'is_admin_usage_enabled' => $isAdminUsageEnabled
            ],
            [
                'last_view_version',
                'is_admin_usage_enabled'
            ]
        );
        return true;
    }

    /**
     * Get log by viewer Id.
     *
     * @param int $viewerId
     * @return Log
     */
    public function get(int $viewerId) : Log
    {
        return $this->logFactory->create(['data' => $this->loadLogData($viewerId)]);
    }

    /**
     * Load release notification viewer log data by viewer id
     *
     * @param int $viewerId
     * @return array
     */
    private function loadLogData(int $viewerId) : array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName(self::LOG_TABLE_NAME))
            ->where('viewer_id = ?', $viewerId);

        $data = $connection->fetchRow($select);
        if (!$data) {
            $data = [];
        }
        return $data;
    }
}
