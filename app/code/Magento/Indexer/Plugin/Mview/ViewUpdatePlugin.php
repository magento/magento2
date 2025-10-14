<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Plugin\Mview;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\ViewInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to prevent view update if the associated indexer or any indexer sharing the same shared_index is suspended.
 */
class ViewUpdatePlugin
{
    /**
     * @var IndexerRegistry
     */
    private IndexerRegistry $indexerRegistry;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $indexerConfig;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ConfigInterface $indexerConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ConfigInterface $indexerConfig,
        LoggerInterface $logger
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->indexerConfig = $indexerConfig;
        $this->logger = $logger;
    }

    /**
     * Skips updating the view if its associated indexer or any indexer with the same shared index is suspended.
     *
     * @param ViewInterface $subject
     * @param callable $proceed
     * @return void
     */
    public function aroundUpdate(ViewInterface $subject, callable $proceed): void
    {
        $viewId = $subject->getId();
        $indexerId = $this->mapViewIdToIndexerId($viewId);

        if ($indexerId === null) {
            $proceed();
            return;
        }

        // Check if the direct indexer or any related indexers via shared_index are suspended
        if ($this->isIndexerOrSharedIndexSuspended($indexerId)) {
            $this->logger->info(
                "Suspended status detected for indexer {$indexerId} or its shared index. "
                . "Any potential update for view {$viewId} will be skipped regardless of backlog status.",
            );
        } else {
            $proceed();
        }
    }

    /**
     * Maps a view ID to its corresponding indexer ID.
     *
     * @param string $viewId
     * @return string|null
     */
    private function mapViewIdToIndexerId(string $viewId): ?string
    {
        foreach ($this->indexerConfig->getIndexers() as $indexerId => $config) {
            if (isset($config['view_id']) && $config['view_id'] === $viewId) {
                return $indexerId;
            }
        }
        return null;
    }

    /**
     * Determines if the specified indexer or any other indexer that shares its shared_index are suspended.
     *
     * @param string $indexerId
     * @return bool
     */
    private function isIndexerOrSharedIndexSuspended(string $indexerId): bool
    {
        $indexer = $this->indexerRegistry->get($indexerId);
        if ($indexer->getStatus() === StateInterface::STATUS_SUSPENDED) {
            return true;
        }

        // Retrieve the shared_index ID from the indexer's configuration
        $sharedIndexId = $this->indexerConfig->getIndexer($indexerId)['shared_index'] ?? null;
        if ($sharedIndexId !== null) {
            foreach ($this->indexerConfig->getIndexers() as $otherIndexerId => $config) {
                if (($config['shared_index'] ?? null) === $sharedIndexId) {
                    $otherIndexer = $this->indexerRegistry->get($otherIndexerId);
                    if ($otherIndexer->getStatus() === StateInterface::STATUS_SUSPENDED) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
