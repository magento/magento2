<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\Config\DependencyInfoProviderInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\ViewInterface;

/**
 * The decorator, which implements logic of the dependency between the indexers.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DependencyDecorator implements IndexerInterface
{
    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var DependencyInfoProviderInterface
     */
    private $dependencyInfoProvider;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param IndexerInterface $indexer
     * @param DependencyInfoProviderInterface $dependencyInfoProvider
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerInterface $indexer,
        DependencyInfoProviderInterface $dependencyInfoProvider,
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexer = $indexer;
        $this->dependencyInfoProvider = $dependencyInfoProvider;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function __call(string $method, array $args)
    {
        return call_user_func_array([$this->indexer, $method], $args);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['indexer', 'dependencyInfoProvider', 'indexerRegistry'];
    }

    /**
     * @inheritdoc
     */
    public function __clone()
    {
        $this->indexer = clone $this->indexer;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->indexer->getId();
    }

    /**
     * @inheritdoc
     */
    public function getViewId(): string
    {
        return $this->indexer->getViewId();
    }

    /**
     * @inheritdoc
     */
    public function getActionClass(): string
    {
        return $this->indexer->getActionClass();
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->indexer->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->indexer->getDescription();
    }

    /**
     * @inheritdoc
     */
    public function getFields(): array
    {
        return $this->indexer->getFields();
    }

    /**
     * @inheritdoc
     */
    public function getSources(): array
    {
        return $this->indexer->getSources();
    }

    /**
     * @inheritdoc
     */
    public function getHandlers(): array
    {
        return $this->indexer->getHandlers();
    }

    /**
     * @inheritdoc
     */
    public function load($indexerId): self
    {
        $this->indexer->load($indexerId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getView(): ViewInterface
    {
        return $this->indexer->getView();
    }

    /**
     * @inheritdoc
     */
    public function getState(): StateInterface
    {
        return $this->indexer->getState();
    }

    /**
     * @inheritdoc
     */
    public function setState(StateInterface $state): self
    {
        $this->indexer->setState($state);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isScheduled(): bool
    {
        return $this->indexer->isScheduled();
    }

    /**
     * @inheritdoc
     */
    public function setScheduled($scheduled)
    {
        $this->indexer->setScheduled($scheduled);
    }

    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return $this->indexer->isValid();
    }

    /**
     * @inheritdoc
     */
    public function isInvalid(): bool
    {
        return $this->indexer->isInvalid();
    }

    /**
     * @inheritdoc
     */
    public function isWorking(): bool
    {
        return $this->indexer->isWorking();
    }

    /**
     * @inheritdoc
     */
    public function invalidate()
    {
        $this->indexer->invalidate();
        $dependentIndexerIds = $this->dependencyInfoProvider->getIndexerIdsToRunAfter($this->indexer->getId());
        foreach ($dependentIndexerIds as $indexerId) {
            $this->indexerRegistry->get($indexerId)->invalidate();
        }
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): string
    {
        return $this->indexer->getStatus();
    }

    /**
     * @inheritdoc
     */
    public function getLatestUpdated(): string
    {
        return $this->indexer->getLatestUpdated();
    }

    /**
     * @inheritdoc
     */
    public function reindexAll()
    {
        $this->indexer->reindexAll();
    }

    /**
     * @inheritdoc
     */
    public function reindexRow($id)
    {
        $this->indexer->reindexRow($id);
        $dependentIndexerIds = $this->dependencyInfoProvider->getIndexerIdsToRunAfter($this->indexer->getId());
        foreach ($dependentIndexerIds as $indexerId) {
            $dependentIndexer = $this->indexerRegistry->get($indexerId);
            if (!$dependentIndexer->isScheduled()) {
                $dependentIndexer->reindexRow($id);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function reindexList($ids)
    {
        $this->indexer->reindexList($ids);
        $dependentIndexerIds = $this->dependencyInfoProvider->getIndexerIdsToRunAfter($this->indexer->getId());
        foreach ($dependentIndexerIds as $indexerId) {
            $dependentIndexer = $this->indexerRegistry->get($indexerId);
            if (!$dependentIndexer->isScheduled()) {
                $dependentIndexer->reindexList($ids);
            }
        }
    }
}
