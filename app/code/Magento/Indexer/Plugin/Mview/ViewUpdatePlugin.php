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
 * Plugin to prevent updating a view if the associated indexer is suspended
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
     * Prevent updating a view if the associated indexer is suspended
     *
    * @param ViewInterface $subject
    * @param callable $proceed
    * @return void
     */
    public function aroundUpdate(ViewInterface $subject, callable $proceed): void
    {
        $indexerId = $this->mapViewIdToIndexerId($subject->getId());

        if ($indexerId === null) {
            $proceed();
            return;
        }

        $indexer = $this->indexerRegistry->get($indexerId);

        if ($indexer->getStatus() != StateInterface::STATUS_SUSPENDED) {
            $proceed();
        } else {
            $this->logger->info(
                "Indexer {$indexer->getId()} is suspended. The view {$subject->getId()} will not be updated."
            );
        }
    }

    /**
     * Map view ID to indexer ID
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
}
