<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;

/**
 * This is the best entry point for both POST and API request
 */
class CollectSourcesForShipmentItems
{
    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * ProcessShipmentItems constructor.
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @internal param ShippingAlgorithmProviderInterface $shippingAlgorithmProvider
     */
    public function __construct(
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        ItemRequestInterfaceFactory $itemRequestFactory,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService
    ) {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->sourceSelectionService = $sourceSelectionService;
    }

    /**
     * @param ShipmentFactory $subject
     * @param callable $proceed
     * @param Order $order
     * @param array $items
     * @param null $tracks
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCreate(
        ShipmentFactory $subject,
        callable $proceed,
        Order $order,
        array $items = [],
        $tracks = null
    ) {
        //TODO: process data from request
        $shipment = $proceed($order, $items, $tracks);
        if (empty($items)) {
            return $shipment;
        }
        //TODO: !!! Temporary decision. Need to implement logic with UI part (get data from items array)
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();
        /** @var \Magento\Sales\Api\Data\ShipmentItemInterface $item */
        foreach ($shipment->getItems() as $item) {
            $requestItem = $this->itemRequestFactory->create([
                    'sku' => $item->getSku(),
                    'qty' => $item->getQty()
            ]);
            $inventoryRequest = $this->inventoryRequestFactory->create([
                'stockId' => $stockId,
                'items' => [$requestItem]
            ]);
            $sourceSelectionResult = $this->sourceSelectionService->execute($inventoryRequest);
            $shippingItemSources = [];
            foreach ($sourceSelectionResult->getSourceItemSelections() as $data) {
                //TODO: need to implement it as Extension Attribute
                $shippingItemSources[] = [
                    'sourceCode' => $data->getSourceCode(),
                    'qtyToDeduct' => $data->getQtyToDeduct()
                ];
            }

            $item->setSources($shippingItemSources);
        }

        return $shipment;
    }
}
