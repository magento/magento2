<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Catalog\Api\GetProductTypeByIdInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\CatalogInventory\Api\Data\StockItemInterface as StockItem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\Stock;

class UpdateStockStatus
{
    /**
     * @var GetProductTypeByIdInterface
     */
    protected $getProductTypeById;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @param GetProductTypeByIdInterface $getProductTypeById
     * @param StockStatusRepositoryInterface $stockStatusRepository
     */
    public function __construct(
        GetProductTypeByIdInterface $getProductTypeById,
        StockStatusRepositoryInterface $stockStatusRepository
    )
    {
        $this->getProductTypeById = $getProductTypeById;
        $this->stockStatusRepository = $stockStatusRepository;
    }

    public function beforeSave(ItemResourceModel $subject, StockItem $stockItem)
    {
        if (
            $stockItem->getIsInStock() &&
            $stockItem->getStockStatusChangedAuto() &&
            $this->getProductTypeById->execute($stockItem->getProductId()) == Configurable::TYPE_CODE
        ) {
            $stockStatus = $this->stockStatusRepository->get($stockItem->getProductId());
            $stockStatus->setStockStatus(Stock::STOCK_IN_STOCK);
            $this->stockStatusRepository->save($stockStatus);
        }

        return null;
    }
}
