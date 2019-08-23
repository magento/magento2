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
     * @var Products\DataProvider\CategoryTree
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
     * @var \Magento\CatalogGraphQl\Model\Category\GetRootCategoryId
     */
    private $getRootCategoryId;

    /**
     * @param Products\DataProvider\CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CheckCategoryIsActive $checkCategoryIsActive
     * @param \Magento\CatalogGraphQl\Model\Category\GetRootCategoryId $getRootCategoryId
     */
    public function __construct(
        Products\DataProvider\CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CheckCategoryIsActive $checkCategoryIsActive,
        \Magento\CatalogGraphQl\Model\Category\GetRootCategoryId $getRootCategoryId
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->checkCategoryIsActive = $checkCategoryIsActive;
        $this->getRootCategoryId = $getRootCategoryId;
    }

    /**
     * Get category id
     *
     * @param array $args
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryId(array $args) : int
    {
        return isset($args['id']) ? (int)$args['id'] : $this->getRootCategoryId->execute();
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
        if ($rootCategoryId !== $this->getRootCategoryId->execute() && $rootCategoryId > 0) {
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
