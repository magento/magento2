<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model;

use Magento\Framework\App\ResourceConnection;

class QuoteResourceWrapper
{
    /**
     * Constructor
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * Check if quote is active.
     *
     * @param int|null $quoteId
     * @return bool
     */
    public function isActive(?int $quoteId): bool
    {
        if (empty($quoteId)) {
            return false;
        }
        $table = $this->resourceConnection->getTableName('quote');
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($table, 'is_active')
            ->where('entity_id = ?', $quoteId);

        return (bool)$connection->fetchOne($select);
    }

    /**
     * Check if quote is persistent.
     *
     * @param int|null $quoteId
     * @return bool
     */
    public function isPersistent(?int $quoteId): bool
    {
        if (empty($quoteId)) {
            return false;
        }
        $table = $this->resourceConnection->getTableName('quote');
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($table, 'is_persistent')
            ->where('entity_id = ?', $quoteId);

        return (bool)$connection->fetchOne($select);
    }
}
