<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Advertisement\Model\ResourceModel\Viewer;

use Magento\Advertisement\Model\Viewer\Log;
use Magento\Framework\App\ResourceConnection;

/**
 * Advertisement viewer log data logger.
 *
 * Saves and retrieves advertisement viewer log data.
 */
class Logger
{
    /**
     * Log table name
     */
    const LOG_TABLE_NAME = 'advertisement_viewer_log';

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var \Magento\Advertisement\Model\Viewer\LogFactory
     */
    private $logFactory;

    /**
     * Logger constructor.
     * @param ResourceConnection $resource
     * @param \Magento\Advertisement\Model\Viewer\LogFactory $logFactory
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Advertisement\Model\Viewer\LogFactory $logFactory
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
     * Load advertisement viewer log data by viewer id
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
