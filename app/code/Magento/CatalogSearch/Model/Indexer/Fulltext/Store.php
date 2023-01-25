<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext;

use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Catalog search indexer plugin for store.
 */
class Store implements ObserverInterface
{
    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var ConfigInterface
     */
    private $indexerConfig;

    /**
     * @param DimensionFactory $dimensionFactory
     * @param ConfigInterface $indexerConfig
     * @param IndexerHandlerFactory $indexerHandlerFactory
     */
    public function __construct(
        DimensionFactory $dimensionFactory,
        ConfigInterface $indexerConfig,
        IndexerHandlerFactory $indexerHandlerFactory
    ) {
        $this->dimensionFactory = $dimensionFactory;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->indexerConfig = $indexerConfig;
    }

    /**
     * Reindex catalog search.
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    private function clearIndex(\Magento\Store\Model\Store $store)
    {
        $dimensions = [
            $this->dimensionFactory->create(['name' => 'scope', 'value' => $store->getId()])
        ];
        $configData = $this->indexerConfig->getIndexer(FulltextIndexer::INDEXER_ID);
        /** @var \Magento\Framework\Indexer\SaveHandler\IndexerInterface $indexHandler */
        $indexHandler = $this->indexerHandlerFactory->create(['data' => $configData]);
        $indexHandler->cleanIndex($dimensions);
    }

    /**
     * Reindex catalog search on store modification.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $observer->getEvent()->getData('store');
        $this->clearIndex($store);
    }
}
