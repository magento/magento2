<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReleaseNotification\Model\ResourceModel\Viewer;

use Magento\ReleaseNotification\Model\Viewer\Log;
use Magento\ReleaseNotification\Model\Viewer\LogFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Release notification viewer log data logger.
 *
 * Saves and retrieves release notification viewer log data.
 *
 * @deprecated Starting from Magento OS 2.4.7 Magento_ReleaseNotification module is deprecated
 * in favor of another in-product messaging mechanism
 * @see Current in-product messaging mechanism
 */
class Logger
{
    /**
     * Release notification log table name
     */
    public const LOG_TABLE_NAME = 'release_notification_viewer_log';

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
     * @return bool
     */
    public function log(int $viewerId, string $lastViewVersion) : bool
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $connection->insertOnDuplicate(
            $this->resource->getTableName(self::LOG_TABLE_NAME),
            [
                'viewer_id' => $viewerId,
                'last_view_version' => $lastViewVersion
            ],
            [
                'last_view_version'
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
