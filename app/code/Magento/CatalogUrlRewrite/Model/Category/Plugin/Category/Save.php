<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use \Magento\Catalog\Model\Category;
use \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use \Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use \Magento\UrlRewrite\Model\UrlPersistInterface;
use \Magento\Store\Model\Store;

/**
 * Generate and save url-rewrites for category if its parent have specified url-key for different store views
 */
class Save
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    private $categoryUrlPathGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     */
    private $categoryUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService
     */
    private $storeViewService;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param StoreViewService $storeViewService
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        StoreViewService $storeViewService
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->storeViewService = $storeViewService;
    }

    /**
     * Perform url updating for different stores
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Closure $proceed,
        \Magento\Catalog\Model\Category $category
    ) {
        $result = $proceed($category);

        $currentStoreId = $category->getStoreId();
        $parentCategoryId = $category->getParentId();
        if ($category->isObjectNew()
            && $this->isGlobalScope($currentStoreId)
            && !$category->isInRootCategoryList()
            && !empty($parentCategoryId)) {
            foreach ($category->getStoreIds() as $storeId) {
                if (!$this->isGlobalScope($storeId)
                    && $this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
                    $storeId,
                    $parentCategoryId,
                    Category::ENTITY
                )) {
                    $category->setStoreId($storeId);
                    $this->updateUrlPathForCategory($category, $categoryResource);
                    $this->urlPersist->replace($this->categoryUrlRewriteGenerator->generate($category));
                }
            }
        }
        return $result;
    }

    /**
     * Check that store id is in global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    private function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * @param Category $category
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     *
     * @return void
     */
    private function updateUrlPathForCategory(Category $category, $categoryResource)
    {
        $category->unsUrlPath();
        $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
        $categoryResource->saveAttribute($category, 'url_path');
    }
}
