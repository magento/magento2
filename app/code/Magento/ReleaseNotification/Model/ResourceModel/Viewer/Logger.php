<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Model\ResourceModel\Viewer;

use Magento\ReleaseNotification\Model\Viewer\Log;
use Magento\ReleaseNotification\Model\Viewer\LogFactory;
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
    const LOG_TABLE_NAME = 'release_notification_viewer_log';

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
     * @return $this
     */
    public function log($viewerId, $lastViewVersion)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $connection->insertOnDuplicate(
            $this->resource->getTableName(self::LOG_TABLE_NAME),
            [
                'viewer_id' => $viewerId,
                'last_view_version' => $lastViewVersion
            ],
            ['viewer_id', 'last_view_version']
        );

        return $this;
    }

    /**
     * Get log by viewer Id.
     *
     * @param int $viewerId
     * @return Log|null
     */
    public function get($viewerId)
    {
        $data = $this->loadLogData($viewerId);
        if (is_array($data)) {
            return $this->logFactory->create(['data' => $data]);
        } else {
            return null;
        }
    }

    /**
     * Load release notification viewer log data by viewer id
     *
     * @param int $viewerId
     * @return array
     */
    private function loadLogData($viewerId)
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from(
                $this->resource->getTableName(self::LOG_TABLE_NAME)
            )->where(
                'viewer_id = ?',
                $viewerId
            )->order(
                'id DESC'
            )->limit(1);

        return $connection->fetchRow($select);
    }
}
