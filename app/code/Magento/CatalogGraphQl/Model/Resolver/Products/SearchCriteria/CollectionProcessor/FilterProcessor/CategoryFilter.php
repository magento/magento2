<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Collection\JoinMinimalPosition;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\App\ObjectManager;
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
     * @var JoinMinimalPosition
     */
    private $joinMinimalPosition;

    /**
     * @param CategoryFactory $categoryFactory
     * @param CategoryResourceModel $categoryResourceModel
     * @param JoinMinimalPosition|null $joinMinimalPosition
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryResourceModel $categoryResourceModel,
        ?JoinMinimalPosition $joinMinimalPosition = null
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->joinMinimalPosition = $joinMinimalPosition
            ?? ObjectManager::getInstance()->get(JoinMinimalPosition::class);
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
        if ($this->isApplicable($filter)) {
            /** @var Collection $collection */
            $conditionType = $filter->getConditionType() ?: self::CONDITION_TYPE_IN;
            $value = $filter->getValue();
            $ids = is_array($value) ? $value : explode(',', (string) $value);
            if (in_array($conditionType, [self::CONDITION_TYPE_EQ, self::CONDITION_TYPE_IN]) && count($ids) === 1) {
                $category = $this->getCategory((int) reset($ids));
                /** This filter adds ability to sort by position*/
                $collection->addCategoryFilter($category);
            } elseif ($conditionType === self::CONDITION_TYPE_IN) {
                $this->joinMinimalPosition->execute($collection, $ids);
            }
            $collection->addCategoriesFilter(
                [
                    $conditionType => array_map('intval', $this->getCategoryIds($ids))
                ]
            );
        }

        return true;
    }

    /**
     * Check whether the filter can be applied
     *
     * @param Filter $filter
     * @return bool
     */
    private function isApplicable(Filter $filter): bool
    {
        /** @var Collection $collection */
        $conditionType = $filter->getConditionType() ?: self::CONDITION_TYPE_IN;

        return $filter->getValue() && in_array($conditionType, self::CONDITION_TYPES);
    }

    /**
     * Returns all children category IDs for anchor categories including the provided categories
     *
     * @param array $values
     * @return array
     */
    private function getCategoryIds(array $values): array
    {
        $categoryIds = [];
        foreach ($values as $value) {
            $category = $this->getCategory((int) $value);
            $children = [];
            $childrenStr = $category->getIsAnchor() ? $category->getChildren(true) : '';
            if ($childrenStr) {
                $children = explode(',', $childrenStr);
            }
            array_push($categoryIds, $value, ...$children);
        }
        return $categoryIds;
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
