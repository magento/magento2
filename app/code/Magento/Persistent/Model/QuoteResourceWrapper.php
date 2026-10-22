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
     * Fetch the is_active and is_persistent flags for a quote in a single query.
     *
     * @param int|null $quoteId
     * @return array{is_active: bool, is_persistent: bool}
     */
    public function getFlags(?int $quoteId): array
    {
        if (empty($quoteId)) {
            return ['is_active' => false, 'is_persistent' => false];
        }
        $table = $this->resourceConnection->getTableName('quote');
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($table, ['is_active', 'is_persistent'])
            ->where('entity_id = ?', $quoteId);
        $row = $connection->fetchRow($select) ?: [];

        return [
            'is_active' => (bool)($row['is_active'] ?? false),
            'is_persistent' => (bool)($row['is_persistent'] ?? false),
        ];
    }
}
