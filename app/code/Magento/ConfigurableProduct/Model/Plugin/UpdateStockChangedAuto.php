<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare (strict_types=1);
namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\GetProductTypeById;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\Framework\Model\AbstractModel as StockItem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Updates stock_status_changed_auto setting for configurable product when it was saved manually
 */
class UpdateStockChangedAuto
{
    /**
     * @var GetProductTypeById
     */
    private $getProductTypeById;

    /**
     * @param GetProductTypeById $getProductTypeById
     */
    public function __construct(GetProductTypeById $getProductTypeById)
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
        if (!$stockItem->getIsInStock() &&
            !$stockItem->hasStockStatusChangedAutomaticallyFlag() &&
            $this->getProductTypeById->execute($stockItem->getProductId()) == Configurable::TYPE_CODE
        ) {
            $stockItem->setStockStatusChangedAuto(0);
        }
    }
}
