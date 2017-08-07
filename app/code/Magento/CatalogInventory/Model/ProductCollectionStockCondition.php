<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\CollectionConditionInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionModifierInterface;

/**
 * Add stock data to each product in product collection
 * and filter products by "In Stock" option, if configuration not allow to show
 * Out of stock Items
 * @since 2.2.0
 */
class ProductCollectionStockCondition implements CollectionModifierInterface
{
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     * @since 2.2.0
     */
    private $stockHelper;

    /**
     * ProductCollectionStockCondition constructor.
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @since 2.2.0
     */
    public function __construct(\Magento\CatalogInventory\Helper\Stock $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function apply(AbstractDb $collection)
    {
        $this->stockHelper->addIsInStockFilterToCollection($collection);
    }
}
