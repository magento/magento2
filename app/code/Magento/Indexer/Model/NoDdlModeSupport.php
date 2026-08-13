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
     * @param ConfigInterface $indexerConfig
     * @param string[] $supportedIndexerIds
     */
    public function __construct(ConfigInterface $indexerConfig, array $supportedIndexerIds = [])
    {
        $this->indexerConfig = $indexerConfig;
        $this->supportedIndexerIds = $supportedIndexerIds;
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
}
