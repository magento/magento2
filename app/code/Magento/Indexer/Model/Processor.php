<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\StateInterface;

/**
 * Class \Magento\Indexer\Model\Processor
 *
 */
class Processor
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @var Indexer\CollectionFactory
     */
    protected $indexersFactory;

    /**
     * @var \Magento\Framework\Mview\ProcessorInterface
     */
    protected $mviewProcessor;

    /**
     * @param ConfigInterface $config
     * @param IndexerFactory $indexerFactory
     * @param Indexer\CollectionFactory $indexersFactory
     * @param \Magento\Framework\Mview\ProcessorInterface $mviewProcessor
     */
    public function __construct(
        ConfigInterface $config,
        IndexerFactory $indexerFactory,
        Indexer\CollectionFactory $indexersFactory,
        \Magento\Framework\Mview\ProcessorInterface $mviewProcessor
    ) {
        $this->config = $config;
        $this->indexerFactory = $indexerFactory;
        $this->indexersFactory = $indexersFactory;
        $this->mviewProcessor = $mviewProcessor;
    }

    /**
     * Regenerate indexes for all invalid indexers
     *
     * @return void
     */
    public function reindexAllInvalid()
    {
        $sharedIndexesComplete = [];
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            /** @var Indexer $indexer */
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $indexerConfig = $this->config->getIndexer($indexerId);
            if ($indexer->isInvalid()) {
                // Skip indexers having shared index that was already complete
                if (!in_array($indexerConfig['shared_index'], $sharedIndexesComplete)) {
                    $indexer->reindexAll();
                } else {
                    /** @var \Magento\Indexer\Model\Indexer\State $state */
                    $state = $indexer->getState();
                    $state->setStatus(StateInterface::STATUS_VALID);
                    $state->save();
                }
                if ($indexerConfig['shared_index']) {
                    $sharedIndexesComplete[] = $indexerConfig['shared_index'];
                }
            }
        }
    }

    /**
     * Regenerate indexes for all indexers
     *
     * @return void
     */
    public function reindexAll()
    {
        /** @var IndexerInterface[] $indexers */
        $indexers = $this->indexersFactory->create()->getItems();
        foreach ($indexers as $indexer) {
            $indexer->reindexAll();
        }
    }

    /**
     * Update indexer views
     *
     * @return void
     */
    public function updateMview()
    {
        $this->mviewProcessor->update('indexer');
    }

    /**
     * Clean indexer view changelogs
     *
     * @return void
     */
    public function clearChangelog()
    {
        $this->mviewProcessor->clearChangelog('indexer');
    }
}
