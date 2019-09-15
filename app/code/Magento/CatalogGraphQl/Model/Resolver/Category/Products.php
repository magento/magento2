<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Filter;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Category products resolver, used by GraphQL endpoints to retrieve products assigned to a category
 */
class Products implements ResolverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Filter
     */
    private $filterQuery;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Builder $searchCriteriaBuilder
     * @param Filter $filterQuery
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Builder $searchCriteriaBuilder,
        Filter $filterQuery
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterQuery = $filterQuery;
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
        $args['filter'] = [
            'category_id' => [
                'eq' => $value['id']
            ]
        ];
        $searchCriteria = $this->searchCriteriaBuilder->build($field->getName(), $args);
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);
        $searchResult = $this->filterQuery->getResult($searchCriteria, $info);

        //possible division by 0
        if ($searchCriteria->getPageSize()) {
            $maxPages = ceil($searchResult->getTotalCount() / $searchCriteria->getPageSize());
        } else {
            $maxPages = 0;
        }

        $currentPage = $searchCriteria->getCurrentPage();
        if ($searchCriteria->getCurrentPage() > $maxPages && $searchResult->getTotalCount() > 0) {
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
                'page_size'    => $searchCriteria->getPageSize(),
                'current_page' => $currentPage,
                'total_pages' => $maxPages
            ]
        ];
        return $data;
    }
}
