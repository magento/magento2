<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree;
use Magento\CatalogGraphQl\Model\Category\CategoryFilter;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

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
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryFilter
     */
    private $categoryFilter;

    /**
     * @var ExtractDataFromCategoryTree
     */
    private $extractDataFromCategoryTree;

    /**
     * @param CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CategoryFilter $categoryFilter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CategoryFilter $categoryFilter,
        CollectionFactory $collectionFactory
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->categoryFilter = $categoryFilter;
        $this->collectionFactory = $collectionFactory;
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

        $rootCategoryIds = [];
        if (!isset($args['filters'])) {
            $rootCategoryIds[] = (int)$store->getRootCategoryId();
        } else {
            $categoryCollection = $this->collectionFactory->create();
            try {
                $this->categoryFilter->applyFilters($args, $categoryCollection, $store);
            } catch (InputException $e) {
                return [];
            }

            foreach ($categoryCollection as $category) {
                $rootCategoryIds[] = (int)$category->getId();
            }
        }

        $result = $this->fetchCategories($rootCategoryIds, $info);
        return $result;
    }

    /**
     * Fetch category tree data
     *
     * @param array $categoryIds
     * @param ResolveInfo $info
     * @return array
     * @throws GraphQlNoSuchEntityException
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
