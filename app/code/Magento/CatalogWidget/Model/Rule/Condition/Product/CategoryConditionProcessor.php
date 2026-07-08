<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\CatalogWidget\Model\Rule\Condition\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Process category condition to include child categories if the category is anchor
 */
class CategoryConditionProcessor
{
    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * Process category condition to include child categories if the category is anchor
     *
     * @param array $condition
     * @param int|null $storeId
     * @return array
     */
    public function process(array $condition, ?int $storeId = null): array
    {
        if (!empty($condition['value'])) {
            $condition['value'] = $this->getCategoriesWithChildren(
                !is_array($condition['value']) ? $this->toArray((string) $condition['value']) : $condition['value'],
                $storeId
            );
        }
        return $condition;
    }

    /**
     * Get category IDs including children of anchor categories
     *
     * @param array $categoryIds
     * @param int|null $storeId
     * @return array
     */
    private function getCategoriesWithChildren(array $categoryIds, ?int $storeId = null): array
    {
        $allCategoryIds = [];
        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId, $storeId);
            } catch (NoSuchEntityException $e) {
                continue;
            }

            $allCategoryIds[] = $categoryId;
            $children = $category->getIsAnchor() ? $category->getChildren(true) : '';
            if ($children) {
                array_push($allCategoryIds, ...$this->toArray((string) $children));
            }
        }

        return $allCategoryIds;
    }

    /**
     * Convert comma or semicolon separated string to array
     *
     * @param string $value
     * @return array
     */
    private function toArray(string $value): array
    {
        return $value ? preg_split('#\s*[,;]\s*#', $value, -1, PREG_SPLIT_NO_EMPTY) : [];
    }
}
