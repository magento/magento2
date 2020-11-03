<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
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
     * @param ConfigInterface $config
     * @param IndexerInterfaceFactory $indexerFactory
     * @param Indexer\CollectionFactory $indexersFactory
     * @param ProcessorInterface $mviewProcessor
     * @param MakeSharedIndexValid|null $makeSharedValid
     */
    public function __construct(
        ConfigInterface $config,
        IndexerInterfaceFactory $indexerFactory,
        Indexer\CollectionFactory $indexersFactory,
        ProcessorInterface $mviewProcessor,
        MakeSharedIndexValid $makeSharedValid = null
    ) {
        $this->config = $config;
        $this->indexerFactory = $indexerFactory;
        $this->indexersFactory = $indexersFactory;
        $this->mviewProcessor = $mviewProcessor;
        $this->makeSharedValid = $makeSharedValid ?: ObjectManager::getInstance()->get(MakeSharedIndexValid::class);
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

            if ($indexer->isInvalid()) {
                // Skip indexers having shared index that was already complete
                $sharedIndex = $indexerConfig['shared_index'] ?? null;
                if (!in_array($sharedIndex, $this->sharedIndexesComplete)) {
                    $indexer->reindexAll();

                    if (!empty($sharedIndex) && $this->makeSharedValid->execute($sharedIndex)) {
                        $this->sharedIndexesComplete[] = $sharedIndex;
                    }
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
