<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogGraphQl\Model\Category\Filter\SearchCriteria;
use Magento\Store\Api\Data\StoreInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CatalogGraphQl\Model\Category\CategoryFilter;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Category List resolver, used for GraphQL category data request processing.
 */
class CategoryList implements ResolverInterface
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
     * @var SearchCriteria
     */
    private $searchCriteria;

    /**
     * @param CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CategoryFilter $categoryFilter
     * @param ArgumentsProcessorInterface $argsSelection
     * @param SearchCriteria $searchCriteria
     */
    public function __construct(
        CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CategoryFilter $categoryFilter,
        ArgumentsProcessorInterface $argsSelection,
        SearchCriteria $searchCriteria
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->categoryFilter = $categoryFilter;
        $this->argsSelection = $argsSelection;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value[$field->getName()])) {
            return $value[$field->getName()];
        }
        $store = $context->getExtensionAttributes()->getStore();

        if (!isset($args['filters'])) {
            $args['filters']['ids'] = ['eq' => $store->getRootCategoryId()];
        }
        try {
            $processedArgs = $this->argsSelection->process($info->fieldName, $args);
            $filterResults = $this->categoryFilter->getResult($processedArgs, $store, [], $context);

            $topLevelCategoryIds = $filterResults['category_ids'];
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $this->fetchCategoriesByTopLevelIds($topLevelCategoryIds, $info, $processedArgs, [], $context);
    }

    /**
     * Fetch category tree data
     *
     * @param array $topLevelCategoryIds
     * @param ResolveInfo $info
     * @param array $processedArgs
     * @param array $attributeNames
     * @param ContextInterface $context
     * @return array
     * @throws LocalizedException
     */
    private function fetchCategoriesByTopLevelIds(
        array $topLevelCategoryIds,
        ResolveInfo $info,
        array $processedArgs,
        array $attributeNames,
        ContextInterface $context
    ) : array {
        // pagination must be applied to top level category results, children categories are not paginated
        $processedArgs['pageSize'] = 0;
        $searchCriteria = $this->searchCriteria->buildCriteria(
            $processedArgs,
            $context->getExtensionAttributes()->getStore()
        );
        $categoryCollection = $this->categoryTree->getFlatCategoriesByRootIds(
            $info,
            $topLevelCategoryIds,
            $searchCriteria,
            $attributeNames,
            $context
        );
        return $this->extractDataFromCategoryTree->buildTree($categoryCollection, $topLevelCategoryIds);
    }
}
