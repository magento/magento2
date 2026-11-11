<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\NoDdlModeInterface;
use Magento\Indexer\Model\NoDdlModeSupport;

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
     * @var NoDdlModeSupport
     */
    private $noDdlModeSupport;

    /**
     * @param NoDdlModeInterface $noDdlMode
     * @param NoDdlModeSupport $noDdlModeSupport
     */
    public function __construct(NoDdlModeInterface $noDdlMode, NoDdlModeSupport $noDdlModeSupport)
    {
        $this->noDdlMode = $noDdlMode;
        $this->noDdlModeSupport = $noDdlModeSupport;
    }

    /**
     * Append the replica suffix when No-DDL reindex mode is enabled and the replica is active
     *
     * @param ResourceConnection $subject
     * @param string $result
     * @param string|bool|string[] $modelEntity
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTableName(ResourceConnection $subject, string $result, $modelEntity): string
    {
        if (!is_string($modelEntity)) {
            return $result;
        }

        $indexerId = $this->noDdlModeSupport->getIndexerIdForTable($modelEntity);
        if ($indexerId === null) {
            return $result;
        }

        if ($this->noDdlMode->isEnabled($indexerId) && !$this->noDdlMode->isMainTableActive($indexerId)) {
            return $result . NoDdlModeInterface::REPLICA_TABLE_SUFFIX;
        }

        return $result;
    }
}
