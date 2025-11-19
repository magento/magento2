<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\ResourceModel\Rule;

use Magento\Framework\App\ResourceConnection;

/**
 * Provides rule IDs for indexing operations
 */
class RuleIdProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get active rule IDs (lightweight query - only IDs)
     *
     * @return array
     */
    public function getActiveRuleIds(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalogrule');

        $select = $connection->select()
            ->from($tableName, ['rule_id'])
            ->where('is_active = ?', 1)
            ->order('sort_order ASC');

        return $connection->fetchCol($select);
    }

    /**
     * Get all rule IDs
     *
     * @return array
     */
    public function getAllRuleIds(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalogrule');

        $select = $connection->select()
            ->from($tableName, ['rule_id'])
            ->order('sort_order ASC');

        return $connection->fetchCol($select);
    }
}
