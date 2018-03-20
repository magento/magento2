<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Categories\Query\Filter;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Promise;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\Builder;
use Magento\CatalogGraphQl\Model\Resolver\Categories\Query\Search;

/**
 * Products field resolver, used for GraphQL request processing.
 */
class CategoryTree implements ResolverInterface
{
    /**
     * Category Tree key in graphql
     */
    const CATEGORY_TREE_KEY = 'children';

    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Search
     */
    private $categoriesSearch;

    /**
     * @var Filter
     */
    private $categoriesFilter;

    /**
     * @var Products\DataProvider\Product
     */
    private $productDataProvider;

    /**
     * CategoryTree constructor.
     * @param Builder $searchCriteriaBuilder
     * @param Search $categoriesSearch
     * @param Filter $categoriesFilter
     * @param Products\DataProvider\Product $productDataProvider
     */
    public function __construct(
        Builder $searchCriteriaBuilder,
        Search $categoriesSearch,
        Filter $categoriesFilter,
        Products\DataProvider\Product $productDataProvider
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoriesSearch = $categoriesSearch;
        $this->categoriesFilter = $categoriesFilter;
        $this->productDataProvider = $productDataProvider;
    }

    /**
     * @param Field $field
     * @param array|null $value
     * @param array|null $args
     * @param $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info)
    {
        if (isset($value[$field->getName()])) {
            return $value[$field->getName()];
        }

        $searchCriteria = $this->searchCriteriaBuilder->build($args);

        if (isset($args['search'])) {
            $categoriesTree = $this->categoriesSearch->getResult($searchCriteria);
        } else {
            $categoriesTree = $this->categoriesFilter->getResult($searchCriteria);
        }

        $processedCategoryTree = $this->processCategoriesTree([$categoriesTree]);
        return ['category_tree' => $processedCategoryTree];
    }

    /**
     * @param array $categoriesTree
     * @return array
     */
    private function processCategoriesTree(array $categoriesTree)
    {
        foreach ($categoriesTree as $nodeKey => $node) {
            if (isset($node['children_data'])) {
                $categoriesTree[$nodeKey][self::CATEGORY_TREE_KEY] = $this->processCategoriesTree($node['children_data']);
                unset($categoriesTree[$nodeKey]['children_data']);
            }

            $categoryProducts = $this->productDataProvider->getListOfProductsInCategories($node['id']);
            $categoriesTree[$nodeKey]['products']['items'] = $categoryProducts;
        }

        return $categoriesTree;
    }
}
