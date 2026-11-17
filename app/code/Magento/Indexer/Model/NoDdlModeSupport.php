<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\ConfigInterface;

/**
 * Reports which indexers declare support for No-DDL reindex mode, and which other indexers must be
 * enabled alongside a given one
 */
class NoDdlModeSupport
{
    /**
     * @var ConfigInterface
     */
    private $indexerConfig;

    /**
     * Indexer IDs that declare support for No-DDL reindex mode, contributed by each adopting module's own di.xml
     *
     * @var string[]
     */
    private $supportedIndexerIds;

    /**
     * Physical table name to indexer ID, pre-filtered to only indexers that declare support, so the
     * per-call table lookup in getIndexerIdForTable() never needs a separate isSupported() scan
     *
     * @var string[]
     */
    private $supportedIndexerTables;

    /**
     * @param ConfigInterface $indexerConfig
     * @param string[] $supportedIndexerIds
     * @param string[] $indexerTables Physical table name to indexer ID, contributed by each adopting
     *        module's own di.xml
     */
    public function __construct(
        ConfigInterface $indexerConfig,
        array $supportedIndexerIds = [],
        array $indexerTables = []
    ) {
        $this->indexerConfig = $indexerConfig;
        $this->supportedIndexerIds = $supportedIndexerIds;
        $this->supportedIndexerTables = array_filter(
            $indexerTables,
            fn (string $indexerId): bool => in_array($indexerId, $supportedIndexerIds, true)
        );
    }

    /**
     * Check whether the given indexer declares support for No-DDL reindex mode
     *
     * @param string $indexerId
     * @return bool
     */
    public function isSupported(string $indexerId): bool
    {
        return in_array($indexerId, $this->supportedIndexerIds, true);
    }

    /**
     * Other supported indexer IDs sharing the given indexer's shared_index group, if any
     *
     * @param string $indexerId
     * @return string[]
     */
    public function getPairedIndexerIds(string $indexerId): array
    {
        $sharedIndex = $this->indexerConfig->getIndexer($indexerId)['shared_index'] ?? null;
        if ($sharedIndex === null) {
            return [];
        }

        $pairedIndexerIds = [];
        foreach ($this->indexerConfig->getIndexers() as $otherIndexerId => $otherIndexerData) {
            if ($otherIndexerId !== $indexerId
                && ($otherIndexerData['shared_index'] ?? null) === $sharedIndex
                && $this->isSupported($otherIndexerId)
            ) {
                $pairedIndexerIds[] = $otherIndexerId;
            }
        }

        return $pairedIndexerIds;
    }

    /**
     * Resolve the indexer ID that owns a given physical table, if that indexer supports No-DDL reindex mode
     *
     * @param string $tableName
     * @return string|null
     */
    public function getIndexerIdForTable(string $tableName): ?string
    {
        return $this->supportedIndexerTables[$tableName] ?? null;
    }
}
