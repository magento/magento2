<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Catalog\Api\GetProductTypeByIdInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class UpdateStockChangedAuto
{
    /**
     * @var GetProductTypeByIdInterface
     */
    protected $getProductTypeById;

    /**
     * UpdateStockChangedAuto constructor
     *
     * @param GetProductTypeByIdInterface $getProductTypeById
     */
    public function __construct(GetProductTypeByIdInterface $getProductTypeById)
    {
        $this->getProductTypeById = $getProductTypeById;
    }

    /**
     * Updates stock_status_changed_auto for configurable product
     *
     * @param ItemResourceModel $subject
     * @param StockItem $stockItem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(ItemResourceModel $subject, StockItem $stockItem): void
    {
        if (
            !$stockItem->getIsInStock() &&
            !$stockItem->hasStockStatusChangedAutomaticallyFlag() &&
            $this->getProductTypeById->execute($stockItem->getProductId()) == Configurable::TYPE_CODE
        ) {
            $stockItem->setStockStatusChangedAuto(0);
        }
    }
}
