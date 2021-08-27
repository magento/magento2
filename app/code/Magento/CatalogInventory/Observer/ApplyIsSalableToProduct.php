<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;

class ApplyIsSalableToProduct implements ObserverInterface
{
    /**
     * @var StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @param StockItemRepository $stockItemRepository
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     */
    public function __construct(
        StockItemRepository $stockItemRepository,
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterface $getReservationsQuantity
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->stockItemRepository = $stockItemRepository;
    }
    /**
     * Apply is salable to product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $salable = $observer->getEvent()->getSalable();

        if ($salable) {
            $stockItemConfig = $this->stockItemRepository->get($product->getId());
            if (null === $stockItemConfig) {
                return $this;
            }
            $stockItemData = $this->getStockItemData->execute($product->getSku(), $stockItemConfig->getStockId());
            if (null === $stockItemData) {
                return $this;
            }
            $minQty = $stockItemConfig->getMinQty();

            $productQtyInStock = $stockItemData['quantity']
                + $this->getReservationsQuantity->execute($product->getSku(), $stockItemConfig->getStockId())
                - $minQty;
            if ($product->getTypeId() == 'simple' && ($productQtyInStock <=0 || $stockItemConfig->getBackorders() !== 0)) {
                $observer->getEvent()->getSalable()->setIsSalable(false);
            }
        }
        return $this;
    }
}
