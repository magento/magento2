<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Provider;

/**
 * Cutoff-aware variant of NotSyncedDataProviderInterface.
 *
 * Accepts an explicit upper-bound timestamp so the caller controls which
 * calendar second is treated as the sync boundary. This prevents the
 * tied-timestamp stale-grid bug where main.updated_at == grid.updated_at
 * causes the strict `>` JOIN to permanently exclude the row.
 *
 * @api
 */
interface NotSyncedDataProviderWithCutoffInterface
{
    /**
     * Returns id grid row is behind the main table, considering only rows with main.updated_at <= $cutoff.
     *
     * @param string $mainTableName source table name
     * @param string $gridTableName grid table name
     * @param string $cutoff upper-bound timestamp rows updated after this value are skipped as in-flight
     * @return array
     */
    public function getIdsWithCutoff($mainTableName, $gridTableName, $cutoff);
}
