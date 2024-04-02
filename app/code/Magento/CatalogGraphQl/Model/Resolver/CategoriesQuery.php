<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\Category\CategoryFilter;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Categories resolver, used for GraphQL category data request processing.
 */
class CategoriesQuery implements ResolverInterface
{
    /**
     * @var CategoryTree
     */
    private $categoryTree;

    /**
     * @var CategoryFilter
     */
    private $categoryFilter;

    /**
     * @var ExtractDataFromCategoryTree
     */
    private $extractDataFromCategoryTree;

    /**
     * @var ArgumentsProcessorInterface
     */
    private $argsSelection;

    /**
     * @var SearchCriteriaFactory
     */
    private $searchCriteriaFactory;

    /**
     * @param CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CategoryFilter $categoryFilter
     * @param ArgumentsProcessorInterface $argsSelection
     * @param SearchCriteriaFactory $searchCriteriaFactory
     */
    public function __construct(
        CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CategoryFilter $categoryFilter,
        ArgumentsProcessorInterface $argsSelection,
        SearchCriteriaFactory $searchCriteriaFactory
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->categoryFilter = $categoryFilter;
        $this->argsSelection = $argsSelection;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $store = $context->getExtensionAttributes()->getStore();

        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if (!isset($args['filters'])) {
            //When no filters are specified, get the root category
            $args['filters']['ids'] = ['eq' => $store->getRootCategoryId()];
        }

        try {
            $processedArgs = $this->argsSelection->process($info->fieldName, $args);
            $filterResult = $this->categoryFilter->getResult($processedArgs, $store, [], $context);
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        $rootCategoryIds = $filterResult['category_ids'] ?? [];

        $filterResult['items'] = $this->fetchCategories($rootCategoryIds, $info, $context);
        return $filterResult;
    }

    /**
     * Fetch category tree data
     *
     * @param array $categoryIds
     * @param ResolveInfo $info
     * @param ContextInterface $context
     * @return array
     */
    private function fetchCategories(
        array $categoryIds,
        ResolveInfo $info,
        ContextInterface $context
    ) {
        $searchCriteria = $this->searchCriteriaFactory->create();
        $categoryCollection = $this->categoryTree->getFlatCategoriesByRootIds(
            $info,
            $categoryIds,
            $searchCriteria,
            [],
            $context
        );
        return $this->extractDataFromCategoryTree->buildTree($categoryCollection, $categoryIds);
    }
}
