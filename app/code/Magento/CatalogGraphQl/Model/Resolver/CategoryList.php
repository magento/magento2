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
use Magento\Framework\Exception\InputException;
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
     * @param CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CategoryFilter $categoryFilter
     * @param ArgumentsProcessorInterface $argsSelection
     */
    public function __construct(
        CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CategoryFilter $categoryFilter,
        ArgumentsProcessorInterface $argsSelection
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->categoryFilter = $categoryFilter;
        $this->argsSelection = $argsSelection;
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

            $rootCategoryIds = $filterResults['category_ids'];
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $this->fetchCategories($rootCategoryIds, $info);
    }

    /**
     * Fetch category tree data
     *
     * @param array $categoryIds
     * @param ResolveInfo $info
     * @return array
     */
    private function fetchCategories(array $categoryIds, ResolveInfo $info)
    {
        $fetchedCategories = [];
        foreach ($categoryIds as $categoryId) {
            $categoryTree = $this->categoryTree->getTree($info, $categoryId);
            if (empty($categoryTree)) {
                continue;
            }
            $fetchedCategories[] = current($this->extractDataFromCategoryTree->execute($categoryTree));
        }

        return $fetchedCategories;
    }
}
