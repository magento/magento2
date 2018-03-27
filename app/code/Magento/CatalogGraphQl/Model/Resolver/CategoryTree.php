<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;

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
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param Products\DataProvider\CategoryTree $categoryTree
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree $categoryTree,
        ValueFactory $valueFactory
    ) {
        $this->categoryTree = $categoryTree;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Assert that filters from search criteria are valid and retrieve root category id
     *
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function assertFiltersAreValidAndGetCategoryRootIds(array $args) : int
    {
        if (!isset($args['filter']['root_category_id'])) {
            throw new GraphQlInputException(__('"root_category_id" filter should be specified'));
        }

        return (int) $args['filter']['root_category_id'];
    }

    /**
     * @param Field $field
     * @param array|null $value
     * @param array|null $args
     * @param $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        $that = $this;

        return $this->valueFactory->create(function () use ($value, $args, $that, $field, $info) {
            if (isset($value[$field->getName()])) {
                return $value[$field->getName()];
            }

            $rootCategoryId = $this->assertFiltersAreValidAndGetCategoryRootIds($args);
            $categoriesTree = $this->categoryTree->getTree($info, $rootCategoryId);
            return [
                'category_tree' => $categoriesTree
            ];
        });
    }
}
