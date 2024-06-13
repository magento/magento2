<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\CollectionConditionInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionModifierInterface;

/**
 * Add stock data to each product in product collection
 * and filter products by "In Stock" option, if configuration not allow to show
 * Out of stock Items
 */
class ProductCollectionStockCondition implements CollectionModifierInterface
{
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * ProductCollectionStockCondition constructor.
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     */
    public function __construct(\Magento\CatalogInventory\Helper\Stock $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    /**
     * @inheritdoc
     */
    public function apply(AbstractDb $collection)
    {
        $this->stockHelper->addIsInStockFilterToCollection($collection);
    }
}
