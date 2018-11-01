<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Event\Observer;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

/**
 * Class observer to initiate generation category url_path
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
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param \Magento\CatalogUrlRewrite\Service\V1\StoreViewService $storeViewService
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        StoreViewService $storeViewService
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->storeViewService = $storeViewService;
    }

    /**
     * Generate Category Url Path
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
     * Update Url Key
     *
     * @param Category $category
     * @param string $urlKey
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updateUrlKey($category, $urlKey)
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
     * Update URL path for children
     *
     * @param Category $category
     * @return void
     */
    protected function updateUrlPathForChildren(Category $category)
    {
        $children = $this->childrenCategoriesProvider->getChildren($category, true);

        if ($this->isGlobalScope($category->getStoreId())) {
            foreach ($children as $child) {
                foreach ($category->getStoreIds() as $storeId) {
                    if ($this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
                        $storeId,
                        $child->getId(),
                        Category::ENTITY
                    )) {
                        $child->setStoreId($storeId);
                        $this->updateUrlPathForCategory($child);
                    }
                }
            }
        } else {
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
     * Update URL path for category
     *
     * @param Category $category
     * @param Category|null $parentCategory
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function updateUrlPathForCategory(Category $category, Category $parentCategory = null)
    {
        $category->unsUrlPath();
        $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category, $parentCategory));
        $category->getResource()->saveAttribute($category, 'url_path');
    }
}
