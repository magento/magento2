<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category\Filter;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Sort;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Search\Model\Query;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Utility to help transform raw criteria data into SearchCriteriaInterface
 */
class SearchCriteria
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
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Builder $searchCriteriaBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Builder $searchCriteriaBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Transform raw criteria data into SearchCriteriaInterface
     *
     * @param array $criteria
     * @param StoreInterface $store
     * @return SearchCriteriaInterface
     * @throws InputException
     */
    public function buildCriteria(array $criteria, StoreInterface $store): SearchCriteriaInterface
    {
        $criteria[Filter::ARGUMENT_NAME] = $this->formatMatchFilters($criteria['filters'], $store);
        $criteria[Filter::ARGUMENT_NAME][CategoryInterface::KEY_IS_ACTIVE] = ['eq' => 1];
        $criteria[Sort::ARGUMENT_NAME][CategoryInterface::KEY_POSITION] = ['ASC'];

        $searchCriteria = $this->searchCriteriaBuilder->build('categoryList', $criteria);
        $pageSize = $criteria['pageSize'] ?? 20;
        $currentPage = $criteria['currentPage'] ?? 1;
        $searchCriteria->setPageSize($pageSize)->setCurrentPage($currentPage);

        return $searchCriteria;
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
