<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

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
            if (empty($resultUrlKey)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid URL key'));
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
