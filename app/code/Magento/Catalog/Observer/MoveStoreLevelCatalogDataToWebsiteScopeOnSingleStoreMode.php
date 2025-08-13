<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexProcessor;
use Magento\Catalog\Model\ResourceModel\CatalogCategoryAndProductResolverOnSingleStoreMode as Resolver;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Move and migrate store level catalog product and category to website level
 */
class MoveStoreLevelCatalogDataToWebsiteScopeOnSingleStoreMode implements ObserverInterface
{
    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Resolver $categoryAndProductResolver
     */
    public function __construct(
        private readonly IndexerRegistry $indexerRegistry,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly Resolver $categoryAndProductResolver
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $changedPaths = (array)$observer->getEvent()->getChangedPaths();
        if (in_array(StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED, $changedPaths, true)
            && $this->scopeConfig->getValue(StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED)
            && $this->storeManager->hasSingleStore()
        ) {
            $store = $this->storeManager->getDefaultStoreView();
            if ($store) {
                $storeId = $store->getId();
                $this->categoryAndProductResolver->migrateCatalogCategoryAndProductTables((int) $storeId);
                $this->invalidateIndexer();
            }
        }
    }

    /**
     * Invalidate related indexer
     */
    private function invalidateIndexer(): void
    {
        $productIndexer = $this->indexerRegistry->get(Product::INDEXER_ID);
        $categoryProductIndexer = $this->indexerRegistry->get(ProductCategoryIndexer::INDEXER_ID);
        $priceIndexer = $this->indexerRegistry->get(PriceIndexProcessor::INDEXER_ID);
        $ruleIndexer = $this->indexerRegistry->get(RuleProductProcessor::INDEXER_ID);
        $productIndexer->invalidate();
        $categoryProductIndexer->invalidate();
        $priceIndexer->invalidate();
        $ruleIndexer->invalidate();
    }
}
