<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Check if Image is currently used in any category as Category Image.
 */
class RedundantCategoryImageChecker
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CategoryListInterface
     */
    private $categoryList;

    public function __construct(
        CategoryListInterface $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Checks if Image is currently used in any category as Category Image.
     *
     * Returns true if not.
     *
     * @param string $imageName
     * @return bool
     */
    public function execute(string $imageName): bool
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('image', $imageName)->create();
        $categories = $this->categoryList->getList($searchCriteria)->getItems();

        return empty($categories);
    }
}
