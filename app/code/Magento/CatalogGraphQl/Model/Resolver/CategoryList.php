<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Catalog\Model\Category;
use Magento\CatalogGraphQl\Model\Resolver\Category\CheckCategoryIsActive;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree;
use Magento\CatalogGraphQl\Model\Category\CategoryFilter;

/**
 * Category tree resolver, used for GraphQL category data request processing.
 */
class CategoryList implements ResolverInterface
{
    /**
     * Name of type in GraphQL
     */
    const CATEGORY_INTERFACE = 'CategoryInterface';

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
     * @var CheckCategoryIsActive
     */
    private $checkCategoryIsActive;

    /**
     * @param CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CheckCategoryIsActive $checkCategoryIsActive
     * @param CategoryFilter $categoryFilter
     */
    public function __construct(
        CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CheckCategoryIsActive $checkCategoryIsActive,
        CategoryFilter $categoryFilter
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->checkCategoryIsActive = $checkCategoryIsActive;
        $this->categoryFilter = $categoryFilter;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value[$field->getName()])) {
            return $value[$field->getName()];
        }

        if (!isset($args['filters'])) {
            throw new GraphQlInputException(
                __( "'filters' input argument is required.")
            );
        }

        $rootCategoryIds = $this->categoryFilter->applyFilters($args);
        $result = [];
        $categoriesTreeData = [];

        foreach ($rootCategoryIds as $rootCategoryId) {
            if ($rootCategoryId !== Category::TREE_ROOT_ID) {
                $this->checkCategoryIsActive->execute($rootCategoryId);
            }
            $categoriesTree = $this->categoryTree->getTree($info, $rootCategoryId);

            if (empty($categoriesTree) || ($categoriesTree->count() == 0)) {
                throw new GraphQlNoSuchEntityException(__('Category doesn\'t exist'));
            }
            $categoriesTreeData[] = $categoriesTree;
        }

        foreach ($categoriesTreeData  as $treeData ) {
            $result[] = $this->extractDataFromCategoryTree->execute($treeData);
        }
        return current($result);
    }
}
