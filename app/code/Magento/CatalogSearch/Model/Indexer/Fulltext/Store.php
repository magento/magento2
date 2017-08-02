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
 * Class \Magento\CatalogSearch\Model\Indexer\Fulltext\Store
 *
 * @since 2.0.0
 */
class Store implements ObserverInterface
{
    /**
     * @var DimensionFactory
     * @since 2.0.0
     */
    private $dimensionFactory;

    /**
     * @var IndexerHandlerFactory
     * @since 2.0.0
     */
    private $indexerHandlerFactory;

    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    private $indexerConfig;

    /**
     * @param DimensionFactory $dimensionFactory
     * @param ConfigInterface $indexerConfig
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @since 2.0.0
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
     * @param \Magento\Store\Model\Store $store
     * @return void
     * @since 2.0.0
     */
    private function clearIndex(\Magento\Store\Model\Store $store)
    {
        $dimensions = [
            $this->dimensionFactory->create(['name' => 'scope', 'value' => $store->getId()])
        ];
        $configData = $this->indexerConfig->getIndexer(FulltextIndexer::INDEXER_ID);
        /** @var \Magento\CatalogSearch\Model\Indexer\IndexerHandler $indexHandler */
        $indexHandler = $this->indexerHandlerFactory->create(['data' => $configData]);
        $indexHandler->cleanIndex($dimensions);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $observer->getEvent()->getData('store');
        $this->clearIndex($store);
    }
}
