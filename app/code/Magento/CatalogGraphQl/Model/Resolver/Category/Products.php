<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;

/**
 * Category products resolver, used by GraphQL endpoints to retrieve products assigned to a category
 */
class Products implements ResolverInterface
{
    /**
     * @var ProductQueryInterface
     */
    private $searchQuery;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchApiCriteriaBuilder;

    /**
     * @param ProductQueryInterface $searchQuery
     * @param SearchCriteriaBuilder $searchApiCriteriaBuilder
     */
    public function __construct(
        ProductQueryInterface $searchQuery,
        SearchCriteriaBuilder $searchApiCriteriaBuilder
    ) {
        $this->searchQuery = $searchQuery;
        $this->searchApiCriteriaBuilder = $searchApiCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        $args['filter'] = [
            'category_id' => [
                'eq' => $value['id']
            ]
        ];
        $searchResult = $this->searchQuery->getResult($args, $info, $context);

        //possible division by 0
        if ($searchResult->getPageSize()) {
            $maxPages = ceil($searchResult->getTotalCount() / $searchResult->getPageSize());
        } else {
            $maxPages = 0;
        }

        $currentPage = $searchResult->getCurrentPage();
        if ($searchResult->getCurrentPage() > $maxPages && $searchResult->getTotalCount() > 0) {
            $currentPage = new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the number of pages available.',
                    [$maxPages]
                )
            );
        }

        $data = [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $searchResult->getProductsSearchResult(),
            'page_info'   => [
                'page_size'    => $searchResult->getPageSize(),
                'current_page' => $currentPage,
                'total_pages' => $maxPages
            ]
        ];
        return $data;
    }
}
