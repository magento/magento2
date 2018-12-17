<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

/**
 * Class observer to initiate generation category url_path.
 */
class CategoryUrlPathAutogeneratorObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider
     */
    protected $childrenCategoriesProvider;

    /**
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService
     */
    protected $storeViewService;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param \Magento\CatalogUrlRewrite\Service\V1\StoreViewService $storeViewService
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        StoreViewService $storeViewService,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->storeViewService = $storeViewService;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Generate Category Url Path.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $useDefaultAttribute = !$category->isObjectNew() && !empty($category->getData('use_default')['url_key']);
        if ($category->getUrlKey() !== false && !$useDefaultAttribute) {
            $resultUrlKey = $this->categoryUrlPathGenerator->getUrlKey($category);
            $this->updateUrlKey($category, $resultUrlKey);
        } else if ($useDefaultAttribute) {
            $resultUrlKey = $category->formatUrlKey($category->getOrigData('name'));
            $this->updateUrlKey($category, $resultUrlKey);
            $category->setUrlKey(null)->setUrlPath(null);
        }
    }

    /**
     * Update Url Key.
     *
     * @param Category $category
     * @param string $urlKey
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateUrlKey(Category $category, string $urlKey)
    {
        if (empty($urlKey)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid URL key'));
        }
        $category->setUrlKey($urlKey)
            ->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
        if (!$category->isObjectNew()) {
            $category->getResource()->saveAttribute($category, 'url_path');
            if ($category->dataHasChangedFor('url_path')) {
                $this->updateUrlPathForChildren($category);
            }
        }
    }

    /**
     * Update URL path for children.
     *
     * @param Category $category
     * @return void
     */
    protected function updateUrlPathForChildren(Category $category)
    {
        if ($this->isGlobalScope($category->getStoreId())) {
            $childrenIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
            foreach ($childrenIds as $childId) {
                foreach ($category->getStoreIds() as $storeId) {
                    if ($this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
                        $storeId,
                        $childId,
                        Category::ENTITY
                    )) {
                        $child = $this->categoryRepository->get($childId, $storeId);
                        $this->updateUrlPathForCategory($child);
                    }
                }
            }
        } else {
            $children = $this->childrenCategoriesProvider->getChildren($category, true);
            foreach ($children as $child) {
                /** @var Category $child */
                $child->setStoreId($category->getStoreId());
                if ($child->getParentId() === $category->getId()) {
                    $this->updateUrlPathForCategory($child, $category);
                } else {
                    $this->updateUrlPathForCategory($child);
                }
            }
        }
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
     * Update URL path for category.
     *
     * @param Category $category
     * @param Category|null $parentCategory
     * @return void
     */
    protected function updateUrlPathForCategory(Category $category, Category $parentCategory = null)
    {
        $category->unsUrlPath();
        $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category, $parentCategory));
        $category->getResource()->saveAttribute($category, 'url_path');
    }
}
