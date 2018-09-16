<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
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
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree $categoryTree
     */
    public function __construct(
        \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree $categoryTree
    ) {
        $this->categoryTree = $categoryTree;
    }

    /**
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
        $categoriesTree = $this->categoryTree->getTree($info, $rootCategoryId);
        if (!empty($categoriesTree)) {
            return current($categoriesTree);
        } else {
            return null;
        }
    }
}
