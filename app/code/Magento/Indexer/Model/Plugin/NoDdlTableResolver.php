<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\NoDdlModeInterface;

/**
 * Appends the No-DDL reindex replica suffix to table name lookups for any indexer that declares support
 */
class NoDdlTableResolver
{
    /**
     * @var NoDdlModeInterface
     */
    private $noDdlMode;

    /**
     * Physical table name to indexer ID, contributed by each adopting indexer's own di.xml
     *
     * @var string[]
     */
    private $tableIndexerMap;

    /**
     * @param NoDdlModeInterface $noDdlMode
     * @param string[] $tableIndexerMap
     */
    public function __construct(NoDdlModeInterface $noDdlMode, array $tableIndexerMap = [])
    {
        $this->noDdlMode = $noDdlMode;
        $this->tableIndexerMap = $tableIndexerMap;
    }

    /**
     * Append the replica suffix when No-DDL reindex mode is enabled and the replica is active
     *
     * @param ResourceConnection $subject
     * @param string $result
     * @param string|string[] $modelEntity
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTableName(ResourceConnection $subject, string $result, $modelEntity): string
    {
        if (is_array($modelEntity) || !isset($this->tableIndexerMap[$modelEntity])) {
            return $result;
        }

        $indexerId = $this->tableIndexerMap[$modelEntity];
        if ($this->noDdlMode->isEnabled($indexerId) && !$this->noDdlMode->isMainTableActive($indexerId)) {
            return $result . NoDdlModeInterface::REPLICA_TABLE_SUFFIX;
        }

        return $result;
    }
}
