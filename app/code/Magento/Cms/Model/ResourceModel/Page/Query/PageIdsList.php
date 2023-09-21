<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\ResourceModel\Page\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class PageIdsList
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns connection.
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }

    /**
     * Get all pages that contain blocks identified by ids or identifiers
     *
     * @param array $ids
     * @return array
     */
    public function execute(array $ids = []): array
    {
        $select = $this->getConnection()->select()
            ->from(
                ['main_table' => $this->resourceConnection->getTableName('cms_page')],
                ['main_table.page_id']
            );
        if (count($ids)) {
            foreach ($ids as $id) {
                $select->orWhere(
                    "MATCH (title, meta_keywords, meta_description, identifier, content)
                    AGAINST ('block_id=\"$id\"')"
                );
            }
            $identifiers = $this->getBlockIdentifiersByIds($ids);
            foreach ($identifiers as $identifier) {
                $select->orWhere(
                    "MATCH (title, meta_keywords, meta_description, identifier, content)
                    AGAINST ('block_id=\"$identifier\"')"
                );
            }
        } else {
            $select->where("MATCH (title, meta_keywords, meta_description, identifier, content)
            AGAINST ('block_id=')");
        }

        return $this->connection->fetchCol($select);
    }

    /**
     * Get blocks identifiers based on ids
     *
     * @param array $ids
     * @return array
     */
    private function getBlockIdentifiersByIds(array $ids): array
    {
        $select = $this->getConnection()->select()
            ->from(
                ['main_table' => $this->resourceConnection->getTableName('cms_block')],
                ['main_table.identifier']
            )->where('block_id IN (?)', $ids, \Zend_Db::INT_TYPE);

        return $this->connection->fetchCol($select);
    }
}
