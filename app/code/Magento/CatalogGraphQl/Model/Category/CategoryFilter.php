<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Sort;
use Magento\Search\Model\Query;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;

/**
 * Category filter allows filtering category results by attributes.
 */
class CategoryFilter
{
    /**
     * @var string
     */
    private const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=%&^';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CategoryListInterface
     */
    private $categoryList;

    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryListInterface $categoryList
     * @param Builder $searchCriteriaBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CategoryListInterface $categoryList,
        Builder $searchCriteriaBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Search for categories
     *
     * @param array $criteria
     * @param StoreInterface $store
     * @return int[]
     * @throws InputException
     */
    public function getResult(array $criteria, StoreInterface $store)
    {
        $categoryIds = [];
        $criteria[Filter::ARGUMENT_NAME] = $this->formatMatchFilters($criteria['filters'], $store);
        $criteria[Filter::ARGUMENT_NAME][CategoryInterface::KEY_IS_ACTIVE] = ['eq' => 1];
        $criteria[Sort::ARGUMENT_NAME][CategoryInterface::KEY_POSITION] = ['ASC'];
        $searchCriteria = $this->searchCriteriaBuilder->build('categoryList', $criteria);
        $pageSize = $criteria['pageSize'] ?? 20;
        $currentPage = $criteria['currentPage'] ?? 1;
        $searchCriteria->setPageSize($pageSize)->setCurrentPage($currentPage);

        $categories = $this->categoryList->getList($searchCriteria);
        foreach ($categories->getItems() as $category) {
            $categoryIds[] = (int)$category->getId();
        }

        $totalPages = 0;
        if ($categories->getTotalCount() > 0 && $searchCriteria->getPageSize() > 0) {
            $totalPages = ceil($categories->getTotalCount() / $searchCriteria->getPageSize());
        }
        if ($searchCriteria->getCurrentPage() > $totalPages && $categories->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchCriteria->getCurrentPage(), $totalPages]
                )
            );
        }

        return [
            'category_ids' => $categoryIds,
            'total_count' => $categories->getTotalCount(),
            'page_info' => [
                'total_pages' => $totalPages,
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $searchCriteria->getCurrentPage(),
            ]
        ];
    }

    /**
     * Format match filters to behave like fuzzy match
     *
     * @param array $filters
     * @param StoreInterface $store
     * @return array
     * @throws InputException
     */
    private function formatMatchFilters(array $filters, StoreInterface $store): array
    {
        $minQueryLength = $this->scopeConfig->getValue(
            Query::XML_PATH_MIN_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        foreach ($filters as $filter => $condition) {
            $conditionType = current(array_keys($condition));
            if ($conditionType === 'match') {
                $searchValue = trim(str_replace(self::SPECIAL_CHARACTERS, '', $condition[$conditionType]));
                $matchLength = strlen($searchValue);
                if ($matchLength < $minQueryLength) {
                    throw new InputException(__('Invalid match filter. Minimum length is %1.', $minQueryLength));
                }
                unset($filters[$filter]['match']);
                $filters[$filter]['like'] = '%' . $searchValue . '%';
            }
        }
        return $filters;
    }
}
