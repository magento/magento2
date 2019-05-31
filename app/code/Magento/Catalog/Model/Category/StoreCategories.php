<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Store\Api\GroupRepositoryInterface;

/**
 * Fetcher for associated with store group categories.
 */
class StoreCategories
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Get all category ids for store.
     *
     * @param int|null $storeGroupId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryIds($storeGroupId = null): array
    {
        $rootCategoryId = $storeGroupId
            ? $this->groupRepository->get($storeGroupId)->getRootCategoryId()
            : Category::TREE_ROOT_ID;
        /** @var Category $rootCategory */
        $rootCategory = $this->categoryRepository->get($rootCategoryId);
        $categoriesIds = $rootCategory->getAllChildren(true);

        return (array) $categoriesIds;
    }
}
