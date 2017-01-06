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
use Magento\Framework\App\ObjectManager;
use Magento\UrlRewrite\Model\UrlRewritesSetFactory;

class CategoryUrlRewriteGenerator
{
    /** Entity type code */
    const ENTITY_TYPE = 'category';

    /** @var StoreViewService */
    protected $storeViewService;

    /**
     * @var \Magento\Catalog\Model\Category
     * @deprecated
     */
    protected $category;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator */
    protected $currentUrlRewritesRegenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator */
    protected $childrenUrlRewriteGenerator;

    /** @var \Magento\UrlRewrite\Model\UrlRewritesSet */
    private $urlRewritesSetPrototype;

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
     * @param \Magento\UrlRewrite\Model\UrlRewritesSetFactory|null $urlRewritesSetFactory
     */
    public function __construct(
        CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator,
        CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator,
        ChildrenUrlRewriteGenerator $childrenUrlRewriteGenerator,
        StoreViewService $storeViewService,
        CategoryRepositoryInterface $categoryRepository,
        UrlRewritesSetFactory $urlRewritesSetFactory = null
    ) {
        $this->storeViewService = $storeViewService;
        $this->canonicalUrlRewriteGenerator = $canonicalUrlRewriteGenerator;
        $this->childrenUrlRewriteGenerator = $childrenUrlRewriteGenerator;
        $this->currentUrlRewritesRegenerator = $currentUrlRewritesRegenerator;
        $this->categoryRepository = $categoryRepository;
        $urlRewritesSetFactory = $urlRewritesSetFactory ?: ObjectManager::getInstance()
            ->get(UrlRewritesSetFactory::class);
        $this->urlRewritesSetPrototype = $urlRewritesSetFactory->create();
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
            ? $this->generateForGlobalScope($category, $overrideStoreUrls, $rootCategoryId)
            : $this->generateForSpecificStoreView($storeId, $category, $rootCategoryId);

        return $urls;
    }

    /**
     * Generate list of urls for global scope
     *
     * @param \Magento\Catalog\Model\Category|null $category
     * @param bool $overrideStoreUrls
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForGlobalScope(Category $category = null, $overrideStoreUrls = false, $rootCategoryId = null)
    {
        $urlRewritesSet = clone $this->urlRewritesSetPrototype;
        $categoryId = $category->getId();
        foreach ($category->getStoreIds() as $storeId) {
            if (!$this->isGlobalScope($storeId)
                && $this->isOverrideUrlsForStore($storeId, $categoryId, $overrideStoreUrls)
            ) {
                $this->updateCategoryUrlForStore($storeId, $category);
                $urlRewritesSet->merge($this->generateForSpecificStoreView($storeId, $category, $rootCategoryId));
            }
        }
        $result = $urlRewritesSet->getData();
        return $result;
    }

    /**
     * @param int $storeId
     * @param int $categoryId
     * @param bool $overrideStoreUrls
     * @return bool
     */
    protected function isOverrideUrlsForStore($storeId, $categoryId, $overrideStoreUrls = false)
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
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category|null $category
     * @return void
     */
    protected function updateCategoryUrlForStore($storeId, Category $category = null)
    {
        $categoryFromRepository = $this->categoryRepository->get($category->getId(), $storeId);
            $category->addData(
                [
                    'url_key' => $categoryFromRepository->getUrlKey(),
                    'url_path' => $categoryFromRepository->getUrlPath()
                ]
            );
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
     * @param string $storeId
     * @param \Magento\Catalog\Model\Category|null $category
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForSpecificStoreView($storeId, Category $category = null, $rootCategoryId = null)
    {
        $urlRewritesSet = clone $this->urlRewritesSetPrototype;
        $urlRewritesSet->merge(
            $this->canonicalUrlRewriteGenerator->generate($storeId, $category)
        );
        $urlRewritesSet->merge(
            $this->childrenUrlRewriteGenerator->generate($storeId, $category, $rootCategoryId)
        );
        $urlRewritesSet->merge(
            $this->currentUrlRewritesRegenerator->generate($storeId, $category, $rootCategoryId)
        );
        return $urlRewritesSet->getData();
    }
}
