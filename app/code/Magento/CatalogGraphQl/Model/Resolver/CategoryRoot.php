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
class CategoryRoot implements ResolverInterface
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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }


    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {

        return $this->getRootCategoryId();
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
