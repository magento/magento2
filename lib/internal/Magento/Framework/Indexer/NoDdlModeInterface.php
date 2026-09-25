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
