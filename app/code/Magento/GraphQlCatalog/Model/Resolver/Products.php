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
use Magento\GraphQlCatalog\Model\Resolver\Products\FilterDataProvider;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchDataProvider;

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
     * @var SearchDataProvider
     */
    private $searchDataProvider;

    /**
     * @var FilterDataProvider
     */
    private $filterDataProvider;

    /**
     * @param Builder $searchCriteriaBuilder
     * @param SearchDataProvider $searchDataProvider
     */
    public function __construct(
        Builder $searchCriteriaBuilder,
        SearchDataProvider $searchDataProvider,
        FilterDataProvider $filterDataProvider
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchDataProvider = $searchDataProvider;
        $this->filterDataProvider = $filterDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $args, ResolverContextInterface $context)
    {
        $searchCriteria = $this->searchCriteriaBuilder->build($args);

        if (isset($args['search'])) {
            $searchResult = $this->searchDataProvider->getResult($searchCriteria);
        } else {
            $searchResult = $this->filterDataProvider->getResult($searchCriteria);
        }
        $itemsResult = $searchResult->getObject();
        $products = $searchResult->getArray();

        $maxPages = ceil($itemsResult->getTotalCount() / $searchCriteria->getPageSize());
        if ($searchCriteria->getCurrentPage() > $maxPages && $itemsResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'The value specified in the currentPage attribute is greater than the number'
                    . ' of pages available (%1).',
                    [$maxPages]
                )
            );
        }

        return [
            'total_count' => $itemsResult->getTotalCount(),
            'items' => $products,
            'page_info' => [
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $searchCriteria->getCurrentPage()
            ]
        ];
    }
}
