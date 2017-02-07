<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\App\ResourceConnection;

/**
 * Provides latest updated entities ids list
 */
class UpdatedIdListProvider implements IdListProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * IdListProvider constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function get($mainTableName, $gridTableName)
    {
        $lastUpdatedAt = $this->getLastUpdatedAtValue($gridTableName);
        $select = $this->getConnection()->select()
            ->from($this->getConnection()->getTableName($mainTableName), ['entity_id'])
            ->where('updated_at >= ?', $lastUpdatedAt);

        return $this->getConnection()->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
    }

    /**
     * Returns update time of the last row in the grid.
     *
     * @param string $gridTableName
     * @return string
     */
    private function getLastUpdatedAtValue($gridTableName)
    {
        $select = $this->getConnection()->select()
            ->from($this->getConnection()->getTableName($gridTableName), ['updated_at'])
            ->order('updated_at DESC')
            ->limit(1);
        $row = $this->getConnection()->fetchRow($select);

        return isset($row['updated_at']) ? $row['updated_at'] : '0000-00-00 00:00:00';
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }
}
