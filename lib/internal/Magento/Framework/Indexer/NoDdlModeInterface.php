<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
 * Manages DDL-free (flag-based) active/reserved table swapping for full reindex
 */
interface NoDdlModeInterface
{
    /**
     * Suffix appended to a table name when its replica (reserved) table is currently active
     *
     * This is a naming contract shared between every indexer's own table maintainer (which physically
     * creates/populates the suffixed table) and the generic ResourceConnection::getTableName() resolver
     * plugin (which appends it for callers that resolve table names without going through the table
     * maintainer directly). Unlike a storage-format detail, both sides must agree on this value.
     */
    public const REPLICA_TABLE_SUFFIX = '_replica';

    /**
     * Check whether No-DDL reindex mode is enabled for the given indexer
     *
     * @param string $indexerId
     * @return bool
     */
    public function isEnabled(string $indexerId): bool;

    /**
     * Enable or disable No-DDL reindex mode for the given indexer
     *
     * @param string $indexerId
     * @param bool $enabled
     * @return void
     */
    public function setEnabled(string $indexerId, bool $enabled): void;

    /**
     * Check whether the main (non-replica) table is currently active for the given indexer
     *
     * @param string $indexerId
     * @return bool
     */
    public function isMainTableActive(string $indexerId): bool;

    /**
     * Flip which physical table (main or replica) is active for the given indexer
     *
     * @param string $indexerId
     * @return void
     */
    public function flipActiveTable(string $indexerId): void;
}
