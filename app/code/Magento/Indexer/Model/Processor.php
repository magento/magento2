<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\ProcessorInterface;
use Magento\Indexer\Model\Processor\MakeSharedIndexValid;

/**
 * Indexer processor
 */
class Processor
{
    /**
     * @var array
     */
    private $sharedIndexesComplete = [];

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var IndexerInterfaceFactory
     */
    protected $indexerFactory;

    /**
     * @var Indexer\CollectionFactory
     */
    protected $indexersFactory;

    /**
     * @var ProcessorInterface
     */
    protected $mviewProcessor;

    /**
     * @var MakeSharedIndexValid
     */
    protected $makeSharedValid;

    /**
     * @var IndexerRegistry
     */
    private IndexerRegistry $indexerRegistry;

    /**
     * @param ConfigInterface $config
     * @param IndexerInterfaceFactory $indexerFactory
     * @param Indexer\CollectionFactory $indexersFactory
     * @param ProcessorInterface $mviewProcessor
     * @param MakeSharedIndexValid|null $makeSharedValid
     * @param IndexerRegistry|null $indexerRegistry
     */
    public function __construct(
        ConfigInterface $config,
        IndexerInterfaceFactory $indexerFactory,
        Indexer\CollectionFactory $indexersFactory,
        ProcessorInterface $mviewProcessor,
        ?MakeSharedIndexValid $makeSharedValid = null,
        ?IndexerRegistry $indexerRegistry = null
    ) {
        $this->config = $config;
        $this->indexerFactory = $indexerFactory;
        $this->indexersFactory = $indexersFactory;
        $this->mviewProcessor = $mviewProcessor;
        $this->makeSharedValid = $makeSharedValid ?: ObjectManager::getInstance()->get(MakeSharedIndexValid::class);
        $this->indexerRegistry = $indexerRegistry ?: ObjectManager::getInstance()->get(IndexerRegistry::class);
    }

    /**
     * Regenerate indexes for all invalid indexers
     *
     * @return void
     */
    public function reindexAllInvalid()
    {
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            /** @var Indexer $indexer */
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $indexerConfig = $this->config->getIndexer($indexerId);

            if ($indexer->isInvalid() && !$indexer->isSuspended()
                && !$this->isSharedIndexSuspended($indexerConfig['shared_index'])
            ) {
                // Skip indexers having shared index that was already complete
                $sharedIndex = $indexerConfig['shared_index'] ?? null;
                if (!in_array($sharedIndex, $this->sharedIndexesComplete)) {
                    $indexer->reindexAll();
                    $indexer->load($indexer->getId());
                    if ($indexer->isValid()) {
                        if (!empty($sharedIndex) && $this->makeSharedValid->execute($sharedIndex)) {
                            $this->sharedIndexesComplete[] = $sharedIndex;
                        }
                    }
                }
            }
        }
    }

    /**
     * Checks if any indexers within a group that share a common 'shared_index' ID are suspended.
     *
     * @param string|null $sharedIndexId
     * @return bool
     */
    private function isSharedIndexSuspended(?string $sharedIndexId): bool
    {
        if ($sharedIndexId === null) {
            return false;
        }

        $indexers = $this->config->getIndexers();

        foreach ($indexers as $indexerId => $config) {
            // Check if the indexer shares the same 'shared_index'
            if (isset($config['shared_index']) && $config['shared_index'] === $sharedIndexId) {
                $indexer = $this->indexerRegistry->get($indexerId);

                // If any indexer that shares the 'shared_index' is suspended, return true
                if ($indexer->getStatus() === StateInterface::STATUS_SUSPENDED) {
                    return true;
                }
            }
        }

        // If none of the shared indexers are suspended, return false
        return false;
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
