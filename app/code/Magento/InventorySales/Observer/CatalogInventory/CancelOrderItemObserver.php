<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Observer\CatalogInventory;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\InventoryCatalog\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

class CancelOrderItemObserver implements ObserverInterface
{
    /**
     * @var Processor
     */
    private $priceIndexer;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @param Processor $priceIndexer
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     */
    public function __construct(
        Processor $priceIndexer,
        SalesEventInterfaceFactory $salesEventFactory,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver
    ) {
        $this->priceIndexer = $priceIndexer;
        $this->salesEventFactory = $salesEventFactory;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getEvent()->getItem();
        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        if ($item->getId() && $item->getProductId() && empty($children) && $qty) {
            $productSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                [$item->getProductId()]
            )[$item->getProductId()];

            $productType = $item->getProductType() ?: $this->getProductTypesBySkus->execute(
                [$productSku]
            )[$productSku];

            if (true === $this->isSourceItemsAllowedForProductType->execute($productType)) {
                /** @var SalesEventInterface $salesEvent */
                $salesEvent = $this->salesEventFactory->create([
                    'type' => SalesEventInterface::EVENT_ORDER_CANCELED,
                    'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
                    'objectId' => (string)$item->getOrderId()
                ]);

                $websiteId = $item->getStore()->getWebsiteId();
                if (null === $websiteId) {
                    throw new LocalizedException(__('$websiteId is required'));
                }

                $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();
                $reservation = $this->reservationBuilder
                    ->setSku($productSku)
                    ->setQuantity((float)$qty)
                    ->setStockId($stockId)
                    ->setMetadata(sprintf(
                        '%s:%s:%s',
                        $salesEvent->getType(),
                        $salesEvent->getObjectType(),
                        $salesEvent->getObjectId()
                    ))
                    ->build();

                $this->appendReservations->execute([$reservation]);
            }
        }
        $this->priceIndexer->reindexRow($item->getProductId());
    }
}
