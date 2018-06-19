<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException as CategoryDoesNotExistException;

/**
 * Based on Magento\Framework\Api\Filter builds condition
 * that can be applied to Catalog\Model\ResourceModel\Product\Collection
 * to filter products by specific categories
 */
class ProductCategoryCondition implements CustomConditionInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * Level that has store root categories
     * @var int
     */
    private $rootCategoryLevel = 1;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param Filter $filter
     * @return string
     */
    public function build(Filter $filter): string
    {
        $categorySelect = $this->resourceConnection->getConnection()->select()
            ->from(
                ['cat' => $this->resourceConnection->getTableName('catalog_category_product')],
                'cat.product_id'
            )->where(
                $this->resourceConnection->getConnection()->prepareSqlCondition(
                    'cat.category_id',
                    [$this->mapConditionType($filter->getConditionType()) => $this->getCategoryIds($filter)]
                )
            );

        $selectCondition = [
            'in' => $categorySelect
        ];

        return $this->resourceConnection->getConnection()
            ->prepareSqlCondition(Collection::MAIN_TABLE_ALIAS . '.entity_id', $selectCondition);
    }

    /**
     * Extracts required category ids from Filter
     * If category is anchor all children categories will be included too
     * If category is root all children categories will be included too
     *
     * @param Filter $filter
     * @return array
     */
    private function getCategoryIds(Filter $filter): array
    {
        $categoryIds = explode(',', $filter->getValue());
        $childCategoryIds = [];

        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (CategoryDoesNotExistException $exception) {
                continue;
            }

            if ($category->getIsAnchor()) {
                $childCategoryIds[] = $category->getAllChildren(true);
            }

            // This is the simplest way to check if category is root
            if ((int)$category->getLevel() === $this->rootCategoryLevel) {
                $childCategoryIds[] = $category->getAllChildren(true);
            }
        }

        return array_unique(array_merge($categoryIds, ...$childCategoryIds));
    }

    /**
     * Map equal and not equal conditions to in and not in
     *
     * @param string $conditionType
     * @return mixed
     */
    private function mapConditionType(string $conditionType): string
    {
        $conditionsMap = [
            'eq'    => 'in',
            'neq'   => 'nin',
            'like'  => 'in',
            'nlike' => 'nin',
        ];
        return $conditionsMap[$conditionType] ?? $conditionType;
    }
}
