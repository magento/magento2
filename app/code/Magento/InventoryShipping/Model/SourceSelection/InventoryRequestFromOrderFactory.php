<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceSelection;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;

class InventoryRequestFromOrderFactory
{
    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * InventoryRequestFactory constructor.
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     */
    public function __construct(
        ItemRequestInterfaceFactory $itemRequestFactory,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        GetSkusByProductIdsInterface $getSkusByProductIds
    ) {
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSkusByProductIds = $getSkusByProductIds;
    }

    /**
     * @param OrderInterface $order
     * @return InventoryRequestInterface
     */
    public function create(OrderInterface $order) : InventoryRequestInterface
    {
        $requestItems = [];
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();

        /** @var OrderItemInterface|OrderItem $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $itemSku = $orderItem->getSku() ?: $this->getSkusByProductIds->execute(
                [$orderItem->getProductId()]
            )[$orderItem->getProductId()];
            $qtyToDeliver = $orderItem->getQtyToShip();

            //check if order item is not delivered yet
            if ($orderItem->isDeleted()
                || $orderItem->getParentItemId()
                || $this->isZero((float)$qtyToDeliver)
                || $orderItem->getIsVirtual()
            ) {
                continue;
            }

            $requestItems[] = $this->itemRequestFactory->create(
                [
                    'sku' => $itemSku,
                    'qty' => $qtyToDeliver
                ]
            );
        }

        return $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items' => $requestItems
        ]);
    }

    /**
     * Compare float number with some epsilon
     *
     * @param float $floatNumber
     *
     * @return bool
     */
    private function isZero(float $floatNumber): bool
    {
        return $floatNumber < 0.0000001;
    }
}
