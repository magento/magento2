<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\Config\DependencyInfoProviderInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;

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
    public function __call($method, $args)
    {
        return $this->indexer->__call($method, $args);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['indexer', 'dependencyProvider', 'indexerRegistry', 'mapperHandler'];
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
    public function getId()
    {
        return $this->indexer->getId();
    }

    /**
     * @inheritdoc
     */
    public function getViewId()
    {
        return $this->indexer->getViewId();
    }

    /**
     * @inheritdoc
     */
    public function getActionClass()
    {
        return $this->indexer->getActionClass();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->indexer->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->indexer->getDescription();
    }

    /**
     * @inheritdoc
     */
    public function getFields()
    {
        return $this->indexer->getFields();
    }

    /**
     * @inheritdoc
     */
    public function getSources()
    {
        return $this->indexer->getSources();
    }

    /**
     * @inheritdoc
     */
    public function getHandlers()
    {
        return $this->indexer->getHandlers();
    }

    /**
     * @inheritdoc
     */
    public function load($indexerId)
    {
        $this->indexer->load($indexerId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getView()
    {
        return $this->indexer->getView();
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->indexer->getState();
    }

    /**
     * @inheritdoc
     */
    public function setState(StateInterface $state)
    {
        return $this->indexer->setState($state);
    }

    /**
     * @inheritdoc
     */
    public function isScheduled()
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
    public function isValid()
    {
        return $this->indexer->isValid();
    }

    /**
     * @inheritdoc
     */
    public function isInvalid()
    {
        return $this->indexer->isInvalid();
    }

    /**
     * @inheritdoc
     */
    public function isWorking()
    {
        return $this->indexer->isWorking();
    }

    /**
     * {@inheritdoc}
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
    public function getStatus()
    {
        return $this->indexer->getStatus();
    }

    /**
     * @inheritdoc
     */
    public function getLatestUpdated()
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
     * {@inheritdoc}
     */
    public function reindexRow($id)
    {
        $this->indexer->reindexRow($id);
        $dependentIndexerIds = $this->dependencyInfoProvider->getIndexerIdsToRunAfter($this->indexer->getId());
        foreach ($dependentIndexerIds as $indexerId) {
            $this->indexerRegistry->get($indexerId)->reindexRow($id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reindexList($ids)
    {
        $this->indexer->reindexList($ids);
        $dependentIndexerIds = $this->dependencyInfoProvider->getIndexerIdsToRunAfter($this->indexer->getId());
        foreach ($dependentIndexerIds as $indexerId) {
            $this->indexerRegistry->get($indexerId)->reindexList($ids);
        }
    }
}
