<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Quote;

use Magento\Framework\App\ResourceConnection;

/**
 * Low-level database delete operations for the quote table.
 */
class Delete
{
    private const QUOTE_TABLE = 'quote';

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * Deletes quote rows by primary key in a single bulk statement.
     *
     * Child rows in quote_address, quote_item, quote_payment, quote_id_mask,
     * and negotiable_quote are removed automatically via ON DELETE CASCADE.
     *
     * @param int[]|string[] $ids
     * @return void
     */
    public function deleteByIds(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName(self::QUOTE_TABLE),
            ['entity_id IN (?)' => $ids]
        );
    }
}
