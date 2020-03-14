<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Service;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * Iterates recursive through the children categories to update `url_path`
 *
 * @api
 */
class CategoryUrlPathUpdateService
{
    /**
     * @var ChildrenCategoriesProvider
     */
    private $childrenProvider;

    /**
     * @var StoreViewService
     */
    private $storeViewService;

    /**
     * @var CategoryUrlPathGenerator
     */
    private $urlPathGenerator;

    /**
     * @var CategoryResourceModel
     */
    private $resourceModel;

    /**
     * @param CategoryUrlPathGenerator $urlPathGenerator
     * @param CategoryResourceModel $resourceModel
     * @param ChildrenCategoriesProvider $childrenProvider
     * @param StoreViewService $storeViewService
     */
    public function __construct(
        CategoryUrlPathGenerator $urlPathGenerator,
        CategoryResourceModel $resourceModel,
        ChildrenCategoriesProvider $childrenProvider,
        StoreViewService $storeViewService
    ) {
        $this->childrenProvider = $childrenProvider;
        $this->storeViewService = $storeViewService;
        $this->urlPathGenerator = $urlPathGenerator;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Iterates recursive through the children categories to update `url_path`
     *
     * @param Category $category
     * @throws NoSuchEntityException
     */
    public function execute(Category $category)
    {
        $childrenCategories = $this->childrenProvider->getChildren($category, true);

        if ($this->isCategoryGlobal($category)) {
            /** @var Category $childCategory */
            foreach ($childrenCategories as $childCategory) {
                foreach ($category->getStoreIds() as $storeId) {
                    if ($this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
                        $storeId,
                        $childCategory->getId(),
                        Category::ENTITY
                    )) {
                        $this->updatePath($childCategory);
                    }
                }
            }
        } else {
            /** @var Category $childCategory */
            foreach ($childrenCategories as $childCategory) {
                $childCategory->setStoreId($category->getStoreId());
                $this->updatePath($childCategory);
            }
        }
    }

    /**
     * Regenerates the `url_path` and saves using Resource Model.
     *
     * @param Category $category
     * @throws NoSuchEntityException
     */
    private function updatePath(Category $category): void
    {
        $category->unsetData('url_path');
        $category->setUrlPath($this->urlPathGenerator->getUrlPath($category));
        $this->resourceModel->saveAttribute($category, 'url_path');
    }

    /**
     * Returns whether Category is loaded for Global scope
     *
     * @param Category $category
     * @return bool
     */
    private function isCategoryGlobal(Category $category): bool
    {
        $storeId = $category->getStoreId();

        // Unfortunately Magento still likes to return `string` as `store_id`.
        return null === $storeId || (string)Store::DEFAULT_STORE_ID === (string)$storeId;
    }
}
