<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Category filter allows to filter products collection using 'category_id' filter from search criteria.
 */
class CategoryFilter implements CustomFilterInterface
{
    /**
     * Equal
     */
    private const CONDITION_TYPE_EQ = 'eq';

    /**
     * Not Equal
     */
    private const CONDITION_TYPE_NEQ = 'neq';

    /**
     * In
     */
    private const CONDITION_TYPE_IN = 'in';

    /**
     * Not In
     */
    private const CONDITION_TYPE_NIN = 'nin';

    /**
     * Supported condition types
     */
    private const CONDITION_TYPES = [
        self::CONDITION_TYPE_EQ,
        self::CONDITION_TYPE_NEQ,
        self::CONDITION_TYPE_IN,
        self::CONDITION_TYPE_NIN,
    ];

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResourceModel
     */
    private $categoryResourceModel;

    /**
     * @param CategoryFactory $categoryFactory
     * @param CategoryResourceModel $categoryResourceModel
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryResourceModel $categoryResourceModel
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResourceModel = $categoryResourceModel;
    }

    /**
     * Apply filter by 'category_id' to product collection.
     *
     * For anchor categories, the products from all children categories will be present in the result.
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter is applied
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        $conditionType = $filter->getConditionType() ?: self::CONDITION_TYPE_IN;
        $value = $filter->getValue();
        if ($value && in_array($conditionType, self::CONDITION_TYPES)) {
            if ($conditionType === self::CONDITION_TYPE_EQ) {
                $category = $this->getCategory((int) $value);
                /** @var Collection $collection */
                /** This filter adds ability to sort by position*/
                $collection->addCategoryFilter($category);
            } elseif (!$collection->getFlag('search_resut_applied')) {
                /** Prevent filtering duplication as the filter should be already applied to the search result */
                $values = is_array($value) ? $value : explode(',', (string) $value);
                $categoryIds = [];
                foreach ($values as $value) {
                    $category = $this->getCategory((int) $value);
                    $children = [];
                    $childrenStr = $category->getIsAnchor() ? $category->getChildren(true) : '';
                    if ($childrenStr) {
                        $children = explode(',',  $childrenStr);
                    }
                    array_push($categoryIds, $value, ...$children);
                }
                /** @var Collection $collection */
                $collection->addCategoriesFilter([$conditionType => array_map('intval', $categoryIds)]);
            }
        }

        return true;
    }

    /**
     * Retrieve the category model by ID
     *
     * @param int $id
     * @return \Magento\Catalog\Model\Category
     */
    private function getCategory(int $id): \Magento\Catalog\Model\Category
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->categoryFactory->create();
        $this->categoryResourceModel->load($category, $id);
        return $category;
    }
}
