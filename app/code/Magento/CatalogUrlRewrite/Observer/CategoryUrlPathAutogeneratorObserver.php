<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Event\Observer;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;

class CategoryUrlPathAutogeneratorObserver implements ObserverInterface
{
    /** @var CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var StoreViewService */
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        if ($category->getUrlKey() !== false) {

            /** @var string $resultUrlKey */
            $resultUrlKey = $this->categoryUrlPathGenerator->getUrlKey($category);

            if (empty($resultUrlKey)) {
                throw new LocalizedException(__('Invalid URL key'));
            }

            $category->setUrlKey($resultUrlKey)
                ->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
            if (!$category->isObjectNew()) {
                $category->getResource()->saveAttribute($category, 'url_path');
                if ($category->dataHasChangedFor('url_path')) {
                    $this->updateUrlPathForChildren($category);
                }
            }
        }
    }

    /**
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
                $child->setStoreId($category->getStoreId());
                $this->updateUrlPathForCategory($child);
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
     * @param Category $category
     * @return void
     */
    protected function updateUrlPathForCategory(Category $category)
    {
        $category->unsUrlPath();
        $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
        $category->getResource()->saveAttribute($category, 'url_path');
    }
}
