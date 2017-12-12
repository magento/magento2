<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver;

use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;
use Magento\GraphQl\Model\ResolverContextInterface;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\Builder;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Model\ContextInterface;
use Magento\GraphQlCatalog\Model\Resolver\Products\Query\Filter;
use Magento\GraphQlCatalog\Model\Resolver\Products\Query\Search;

/**
 * Products field resolver, used for GraphQL request processing.
 */
class Products implements ResolverInterface
{
    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Search
     */
    private $searchQuery;

    /**
     * @var Filter
     */
    private $filterQuery;

    /**
     * @param Builder $searchCriteriaBuilder
     * @param Search $searchQuery
     * @param Filter $filterQuery
     */
    public function __construct(
        Builder $searchCriteriaBuilder,
        Search $searchQuery,
        Filter $filterQuery
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchQuery = $searchQuery;
        $this->filterQuery = $filterQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $args, ResolverContextInterface $context)
    {
        $searchCriteria = $this->searchCriteriaBuilder->build($args);

        if (isset($args['search'])) {
            $searchResult = $this->searchQuery->getResult($searchCriteria);
        } else {
            $searchResult = $this->filterQuery->getResult($searchCriteria);
        }

        $maxPages = ceil($searchResult->getTotalCount() / $searchCriteria->getPageSize());
        if ($searchCriteria->getCurrentPage() > $maxPages && $searchResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'The value specified in the currentPage attribute is greater than the number'
                    . ' of pages available (%1).',
                    [$maxPages]
                )
            );
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $searchResult->getProductsSearchResult(),
            'page_info' => [
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $searchCriteria->getCurrentPage()
            ]
        ];
    }
}
