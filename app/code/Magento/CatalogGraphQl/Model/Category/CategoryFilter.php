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
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match;
use Magento\Search\Model\Query;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;

/**
 * Category filter allows to filter collection using 'id, url_key, name' from search criteria.
 */
class CategoryFilter
{
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
     * Search for categories, return list of ids
     *
     * @param array $criteria
     * @param StoreInterface $store
     * @return int[]
     * @throws InputException
     */
    public function getResult(array $criteria, StoreInterface $store): array
    {
        $categoryIds = [];

        $criteria[Filter::ARGUMENT_NAME] = $this->formatMatchFilters($criteria['filters'], $store);
        $criteria[Filter::ARGUMENT_NAME][CategoryInterface::KEY_IS_ACTIVE] = ['eq' => 1];
        $searchCriteria = $this->searchCriteriaBuilder->build('categoryList', $criteria);
        $categories = $this->categoryList->getList($searchCriteria);
        foreach ($categories->getItems() as $category) {
            $categoryIds[] = (int)$category->getId();
        }
        return $categoryIds;
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
                $searchValue = trim(str_replace(Match::SPECIAL_CHARACTERS, '', $condition[$conditionType]));
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
