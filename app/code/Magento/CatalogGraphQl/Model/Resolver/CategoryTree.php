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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $rootCategoryId = null;

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CheckCategoryIsActive $checkCategoryIsActive
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Products\DataProvider\CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CheckCategoryIsActive $checkCategoryIsActive,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->checkCategoryIsActive = $checkCategoryIsActive;
        $this->storeManager = $storeManager;
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
        return isset($args['id']) ? (int)$args['id'] : $this->getRootCategoryId();
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
        if ($rootCategoryId !== $this->getRootCategoryId() && $rootCategoryId > 0) {
            $this->checkCategoryIsActive->execute($rootCategoryId);
        }
        $categoriesTree = $this->categoryTree->getTree($info, $rootCategoryId);

        if (empty($categoriesTree) || ($categoriesTree->count() == 0)) {
            throw new GraphQlNoSuchEntityException(__('Category doesn\'t exist'));
        }

        $result = $this->extractDataFromCategoryTree->execute($categoriesTree);
        return current($result);
    }

    /**
     * Get Root Category Id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRootCategoryId()
    {
        if ($this->rootCategoryId == null) {
            $this->rootCategoryId = (int)$this->storeManager->getStore()->getRootCategoryId();
        }

        return $this->rootCategoryId;
    }
}
