<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Plugin\Model\ResourceModel\Fulltext;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\OutOfStockInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Zend_Db_Select;

class CollectionPlugin
{
    public const OUT_OF_STOCK_TO_BOTTOM = 2;

    /**
     * @var array
     */
    private $skipFlags = [];

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Data
     */
    private $categoryHelper;

    /**
     * @var OutOfStockInterface
     */
    private $outOfStock;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param Configuration $configuration
     * @param Data $categoryHelper
     * @param OutOfStockInterface $outOfStock
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Configuration $configuration,
        Data $categoryHelper,
        OutOfStockInterface $outOfStock,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->configuration = $configuration;
        $this->categoryHelper = $categoryHelper;
        $this->outOfStock = $outOfStock;
        $this->categoryRepository = $categoryRepository;
    }

    public function beforeSetOrder(
        Collection $subject,
        $attribute,
        $dir = Zend_Db_Select::SQL_DESC
    ): array {
        if (!$subject->getFlag('is_sorted_by_oos')) {
            $subject->setFlag('is_sorted_by_oos', true);
            $currentCategory = $this->categoryHelper->getCategory();
            /* @var Category $category */
            $category = $this->categoryRepository->get($currentCategory->getId());

            if ($this->outOfStock->isOutOfStockBottom($category, $subject)
                && $this->configuration->isShowOutOfStock($subject->getStoreId())) {
                $subject->addAttributeToSort('is_out_of_stock', Zend_Db_Select::SQL_DESC);
            }
        }

        $flagName = $this->_getFlag($attribute);

        if ($subject->getFlag($flagName)) {
            $this->skipFlags[] = $flagName;
        }

        return [$attribute, $dir];
    }

    public function aroundSetOrder(
        Collection $subject,
        callable $proceed,
        $attribute,
        string $dir = Zend_Db_Select::SQL_DESC
    ): Collection {
        $flagName = $this->_getFlag($attribute);
        if (!in_array($flagName, $this->skipFlags, true)) {
            $proceed($attribute, $dir);
        }

        return $subject;
    }

    /**
     * @return bool
     */
    private function isOutOfStockBottom():bool
    {
        //It'll work only when EE repository will be there
        $attributeCode = 'automatic_sorting';
        $currentCategory = $this->categoryHelper->getCategory();

        return (int)$currentCategory->getData($attributeCode) === self::OUT_OF_STOCK_TO_BOTTOM;
    }

    /**
     * Get flag by attribute
     *
     * @param string $attribute
     * @return string
     */
    private function _getFlag(string $attribute): string
    {
        return 'sorted_by_' . $attribute;
    }

    /**
     * Apply sort orders
     *
     * @param Collection $collection
     */
    private function applyOutOfStockAtLastOrders(Collection $collection)
    {
        if (!$collection->getFlag('is_sorted_by_oos')) {
            $collection->setFlag('is_sorted_by_oos', true);
            $collection->setOrder('out_of_stock_at_last', Zend_Db_Select::SQL_DESC);
        }
    }
}
