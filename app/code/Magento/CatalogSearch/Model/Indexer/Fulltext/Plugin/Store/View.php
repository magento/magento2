<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Indexer\Model\ConfigInterface;
use Magento\Indexer\Model\IndexerRegistry;
use Magento\Store\Model\Resource\Store;

class View extends AbstractPlugin
{
    /**
     * @var ConfigInterface
     */
    private $indexerConfig;
    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;
    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ConfigInterface $indexerConfig
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ConfigInterface $indexerConfig,
        IndexerHandlerFactory $indexerHandlerFactory,
        DimensionFactory $dimensionFactory
    ) {
        parent::__construct($indexerRegistry);
        $this->indexerConfig = $indexerConfig;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * Invalidate indexer on store view save
     *
     * @param Store $subject
     * @param \Closure $proceed
     * @param AbstractModel $store
     *
     * @return Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        Store $subject,
        \Closure $proceed,
        AbstractModel $store
    ) {
        $needInvalidation = $store->isObjectNew();
        $result = $proceed($store);
        if ($needInvalidation) {
            $subject->addCommitCallback(
                function () use ($store) {
                    $this->clearIndex($store);
                }
            );
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }

    /**
     * Invalidate indexer on store view delete
     *
     * @param Store $subject
     * @param Store $result
     *
     * @return Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        Store $subject,
        Store $result
    ) {
        $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        return $result;
    }

    /**
     * @param AbstractModel $store
     * @return void
     */
    public function clearIndex(AbstractModel $store)
    {
        $dimensions = [
            $this->dimensionFactory->create(['name' => 'scope', 'value' => $store->getId()])
        ];
        $configData = $this->indexerConfig->getIndexer(Fulltext::INDEXER_ID);
        /** @var \Magento\CatalogSearch\Model\Indexer\IndexerHandler $indexHandler */
        $indexHandler = $this->indexerHandlerFactory->create(['data' => $configData]);
        $indexHandler->cleanIndex($dimensions);
    }
}
