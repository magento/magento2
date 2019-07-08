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
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Category tree field resolver, used for GraphQL request processing.
 */
class CategoryTree implements ResolverInterface
{
    /**
     * Name of type in GraphQL
     */
    const CATEGORY_INTERFACE = 'CategoryInterface';

    /**
     * @var \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree
     */
    private $categoryTree;

    /**
     * @var ExtractDataFromCategoryTree
     */
    private $extractDataFromCategoryTree;

    /**
     * @var CheckCategoryIsActive
     */
    private $checkCategoryIsActive;

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CheckCategoryIsActive $checkCategoryIsActive
     */
    public function __construct(
        \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CheckCategoryIsActive $checkCategoryIsActive
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->checkCategoryIsActive = $checkCategoryIsActive;
    }

    /**
     * Get category id
     *
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getCategoryId(array $args) : int
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"id for category should be specified'));
        }

        return (int)$args['id'];
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value[$field->getName()])) {
            return $value[$field->getName()];
        }

        $rootCategoryId = $this->getCategoryId($args);
        if ($rootCategoryId !== Category::TREE_ROOT_ID) {
            $this->checkCategoryIsActive->execute($rootCategoryId);
        }
        $categoriesTree = $this->categoryTree->getTree($info, $rootCategoryId);

        if (empty($categoriesTree) || ($categoriesTree->count() == 0)) {
            throw new GraphQlNoSuchEntityException(__('Category doesn\'t exist'));
        }

        $result = $this->extractDataFromCategoryTree->execute($categoriesTree);
        return current($result);
    }
}
