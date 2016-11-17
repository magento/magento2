<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Store\Model\Store;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class CategoryUrlRewriteGenerator
{
    /** Entity type code */
    const ENTITY_TYPE = 'category';

    /** @var StoreViewService */
    protected $storeViewService;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator */
    protected $currentUrlRewritesRegenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator */
    protected $childrenUrlRewriteGenerator;

    /**
     * @var bool
     */
    protected $overrideStoreUrls;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator $childrenUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Service\V1\StoreViewService $storeViewService
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator,
        CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator,
        ChildrenUrlRewriteGenerator $childrenUrlRewriteGenerator,
        StoreViewService $storeViewService,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeViewService = $storeViewService;
        $this->canonicalUrlRewriteGenerator = $canonicalUrlRewriteGenerator;
        $this->childrenUrlRewriteGenerator = $childrenUrlRewriteGenerator;
        $this->currentUrlRewritesRegenerator = $currentUrlRewritesRegenerator;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $overrideStoreUrls
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate(Category $category, $overrideStoreUrls = false, $rootCategoryId = null)
    {
        if ($rootCategoryId === null) {
            $rootCategoryId = $category->getEntityId();
        }

        $storeId = $category->getStoreId();
        $urls = $this->isGlobalScope($storeId)
            ? $this->generateForGlobalScope($category, $rootCategoryId, $overrideStoreUrls)
            : $this->generateForSpecificStoreView($category, $storeId, $rootCategoryId);

        return $urls;
    }

    /**
     * Generate list of urls for global scope
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $overrideStoreUrls
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForGlobalScope(Category $category, $overrideStoreUrls, $rootCategoryId = null)
    {
        $urls = [];
        $categoryId = $category->getId();
        foreach ($category->getStoreIds() as $storeId) {
            if (!$this->isGlobalScope($storeId)
                && $this->isOverrideUrlsForStore($storeId, $categoryId, $overrideStoreUrls)
            ) {
                $this->updateCategoryUrlForStore($category, $storeId);
                $specificStoreArray = $this->generateForSpecificStoreView($category, $storeId, $rootCategoryId);
                foreach ($specificStoreArray as $url) {
                    $urls[$url->getRequestPath() . '_' . $url->getStoreId()] = $url;
                }
                unset($specificStoreArray);
            }
        }
        return $urls;
    }

    /**
     * @param int $storeId
     * @param int $categoryId
     * @param bool $overrideStoreUrls
     * @return bool
     */
    protected function isOverrideUrlsForStore($storeId, $categoryId, $overrideStoreUrls)
    {
        return $overrideStoreUrls || !$this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
            $storeId,
            $categoryId,
            Category::ENTITY
        );
    }

    /**
     * Override url key and url path for category in specific Store
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return void
     */
    protected function updateCategoryUrlForStore(Category $category, $storeId)
    {
        $object = $this->categoryRepository->get($category->getId(), $storeId);
        $category->addData(['url_key' => $object->getUrlKey(), 'url_path' => $object->getUrlPath()]);
    }

    /**
     * Check is global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    protected function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * Generate list of urls per store
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param string $storeId
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForSpecificStoreView(Category $category, $storeId, $rootCategoryId = null)
    {
        $urls = [];
        $canonicalUrl = $this->canonicalUrlRewriteGenerator->generate($storeId, $category);
        foreach ($canonicalUrl as $url) {
            $urls[$url->getRequestPath() . '_' . $url->getStoreId()] = $url;
        }
        unset($canonicalUrl);

        $childrenUrl = $this->childrenUrlRewriteGenerator->generate($storeId, $category, $rootCategoryId);
        foreach ($childrenUrl as $url) {
            $urls[$url->getRequestPath() . '_' . $url->getStoreId()] = $url;
        }
        unset($childrenUrl);

        $currentUrl = $this->currentUrlRewritesRegenerator->generate($storeId, $category, $rootCategoryId);
        foreach ($currentUrl as $url) {
            $urls[$url->getRequestPath() . '_' . $url->getStoreId()] = $url;
        }
        unset($currentUrl);
        return $urls;
    }
}
