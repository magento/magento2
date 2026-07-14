<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

/**
 * Manages DDL-free (flag-based) active/reserved table swapping for full reindex
 */
interface NoDdlModeInterface
{
    /**
     * Config path mask (sprintf with the indexer ID) controlling whether No-DDL reindex mode is enabled
     */
    public const XML_PATH_NO_DDL_REINDEX_MASK = 'indexer/%s/no_ddl_reindex';

    /**
     * Check whether No-DDL reindex mode is enabled for the given indexer
     *
     * @param string $indexerId
     * @return bool
     */
    public function isEnabled(string $indexerId): bool;

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
