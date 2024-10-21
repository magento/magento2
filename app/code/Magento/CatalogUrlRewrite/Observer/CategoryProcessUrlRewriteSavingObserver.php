<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory;
use Magento\Store\Model\ResourceModel\Group\Collection as StoreGroupCollection;
use Magento\Framework\App\ObjectManager;

/**
 * Generates Category Url Rewrites after save and Products Url Rewrites assigned to the category that's being saved
 */
class CategoryProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     */
    private $categoryUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer
     */
    private $urlRewriteBunchReplacer;

    /**
     * @var \Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler
     */
    private $urlRewriteHandler;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool
     */
    private $databaseMapPool;

    /**
     * @var string[]
     */
    private $dataUrlRewriteClassNames;

    /**
     * @var CollectionFactory
     */
    private $storeGroupFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlRewriteHandler $urlRewriteHandler
     * @param UrlRewriteBunchReplacer $urlRewriteBunchReplacer
     * @param DatabaseMapPool $databaseMapPool
     * @param ScopeConfigInterface $scopeConfig
     * @param string[] $dataUrlRewriteClassNames
     * @param CollectionFactory|null $storeGroupFactory
     */
    public function __construct(
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlRewriteHandler $urlRewriteHandler,
        UrlRewriteBunchReplacer $urlRewriteBunchReplacer,
        DatabaseMapPool $databaseMapPool,
        ScopeConfigInterface $scopeConfig,
        $dataUrlRewriteClassNames = [
            DataCategoryUrlRewriteDatabaseMap::class,
            DataProductUrlRewriteDatabaseMap::class
        ],
        CollectionFactory $storeGroupFactory = null
    ) {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlRewriteHandler = $urlRewriteHandler;
        $this->urlRewriteBunchReplacer = $urlRewriteBunchReplacer;
        $this->databaseMapPool = $databaseMapPool;
        $this->dataUrlRewriteClassNames = $dataUrlRewriteClassNames;
        $this->storeGroupFactory = $storeGroupFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getData('category');
        if ($category->getParentId() == Category::TREE_ROOT_ID) {
            return;
        }

        if (!$category->hasData('store_id')) {
            $this->setCategoryStoreId($category);
        }

        $mapsGenerated = false;
        if ($this->isCategoryHasChanged($category)) {
            if ($category->dataHasChangedFor('url_key')) {
                $categoryUrlRewriteResult = $this->categoryUrlRewriteGenerator->generate($category);
                $this->urlRewriteBunchReplacer->doBunchReplace($categoryUrlRewriteResult);
            }
            if ($this->isCategoryRewritesEnabled()) {
                if ($this->isChangedOnlyProduct($category)) {
                    $productUrlRewriteResult =
                        $this->urlRewriteHandler->updateProductUrlRewritesForChangedProduct($category);
                    $this->urlRewriteBunchReplacer->doBunchReplace($productUrlRewriteResult);
                } else {
                    $productUrlRewriteResult = $this->urlRewriteHandler->generateProductUrlRewrites($category);
                    $this->urlRewriteBunchReplacer->doBunchReplace($productUrlRewriteResult);
                }
            }
            $mapsGenerated = true;
        }

        //frees memory for maps that are self-initialized in multiple classes that were called by the generators
        if ($mapsGenerated) {
            $this->resetUrlRewritesDataMaps($category);
        }
    }

    /**
     * Check is category changed changed.
     *
     * @param Category $category
     * @return bool
     */
    private function isCategoryHasChanged(Category $category): bool
    {
        if ($category->dataHasChangedFor('url_key')
            || $category->dataHasChangedFor('is_anchor')
            || !empty($category->getChangedProductIds())) {
            return true;
        }
        return false;
    }

    /**
     * Check is only product changed.
     *
     * @param Category $category
     * @return bool
     */
    private function isChangedOnlyProduct(Category $category): bool
    {
        if (!empty($category->getChangedProductIds())
            && !$category->dataHasChangedFor('is_anchor')
            && !$category->dataHasChangedFor('url_key')) {
            return true;
        }
        return false;
    }

    /**
     * In case store_id is not set for category then we can assume that it was passed through product import.
     * Store group must have only one root category, so receiving category's path and checking if one of it parts
     * is the root category for store group, we can set default_store_id value from it to category.
     * it prevents urls duplication for different stores
     * ("Default Category/category/sub" and "Default Category2/category/sub")
     *
     * @param Category $category
     * @return void
     */
    private function setCategoryStoreId($category)
    {
        /** @var StoreGroupCollection $storeGroupCollection */
        $storeGroupCollection = $this->storeGroupFactory->create();

        foreach ($storeGroupCollection as $storeGroup) {
            /** @var \Magento\Store\Model\Group $storeGroup */
            if (in_array($storeGroup->getRootCategoryId(), explode('/', $category->getPath() ?? ''))) {
                $category->setStoreId($storeGroup->getDefaultStoreId());
            }
        }
    }

    /**
     * Resets used data maps to free up memory and temporary tables
     *
     * @param Category $category
     * @return void
     */
    private function resetUrlRewritesDataMaps($category)
    {
        foreach ($this->dataUrlRewriteClassNames as $className) {
            $this->databaseMapPool->resetMap($className, $category->getEntityId());
        }
    }

    /**
     * Check config value of generate_category_product_rewrites
     *
     * @return bool
     */
    private function isCategoryRewritesEnabled()
    {
        return (bool)$this->scopeConfig->getValue('catalog/seo/generate_category_product_rewrites');
    }
}
