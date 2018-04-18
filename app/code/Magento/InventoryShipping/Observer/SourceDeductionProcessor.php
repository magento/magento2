<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\InputException;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventoryShipping\Model\SourceDeduction\Request\ItemToDeductInterfaceFactory;
use Magento\InventoryShipping\Model\SourceDeduction\Request\SourceDeductionRequestInterfaceFactory;
use Magento\InventoryShipping\Model\SourceDeduction\SourceDeductionServiceInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalog\Model\DefaultSourceProvider;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * Class SourceDeductionProcessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceDeductionProcessor implements ObserverInterface
{
    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var ItemToDeductInterfaceFactory
     */
    private $itemToDeduct;

    /**
     * @var SourceDeductionRequestInterfaceFactory
     */
    private $sourceDeductionRequestFactory;

    /**
     * @var SourceDeductionServiceInterface
     */
    private $sourceDeductionService;

    /**
     * @var DefaultSourceProvider
     */
    private $defaultSourceProvider;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param Json $jsonSerializer
     * @param ItemToDeductInterfaceFactory $itemToDeduct
     * @param SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory
     * @param SourceDeductionServiceInterface $sourceDeductionService
     * @param DefaultSourceProvider $defaultSourceProvider
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     */
    public function __construct(
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        Json $jsonSerializer,
        ItemToDeductInterfaceFactory $itemToDeduct,
        SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory,
        SourceDeductionServiceInterface $sourceDeductionService,
        DefaultSourceProvider $defaultSourceProvider,
        SalesEventInterfaceFactory $salesEventFactory,
        IsSingleSourceModeInterface $isSingleSourceMode
    ) {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->jsonSerializer = $jsonSerializer;
        $this->itemToDeduct = $itemToDeduct;
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->salesEventFactory = $salesEventFactory;
        $this->isSingleSourceMode = $isSingleSourceMode;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        $shipmentItem = $observer->getShipmentItem();

        if ($shipmentItem->getOrigData('entity_id')) {
            return;
        }

        $shipment = $shipmentItem->getShipment();

        //TODO: I'm not sure that is good idea (with default source code)...
        if (!empty($shipment->getExtensionAttributes())
            || $shipment->getExtensionAttributes()->getSourceCode()) {
            $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
        } elseif ($this->isSingleSourceMode->execute()) {
            $sourceCode = $this->defaultSourceProvider->getCode();
        }

        $websiteId = $shipment->getOrder()->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();

        $salesEvent = $this->salesEventFactory->create([
            'type' => SalesEventInterface::EVENT_SHIPMENT_CREATED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => $shipment->getOrderId()
        ]);

        $sourceDeductionRequest = $this->sourceDeductionRequestFactory->create([
            'stockId' => $stockId,
            'sourceCode' => $sourceCode,
            'items' => $this->getItemToShip($shipmentItem),
            'salesEvent' => $salesEvent
        ]);
        $this->sourceDeductionService->execute($sourceDeductionRequest);
    }

    /**
     * @param $shipmentItem
     * @return array
     * @throws InputException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getItemToShip($shipmentItem)
    {
        $orderItem = $shipmentItem->getOrderItem();
        $itemSku = $shipmentItem->getSku() ?: $this->getSkusByProductIds->execute(
            [$shipmentItem->getProductId()]
        )[$shipmentItem->getProductId()];
        $qty = $this->castQty($orderItem, $shipmentItem->getQty());
        $itemsToShip = [];
        if ($orderItem->getHasChildren() && !$orderItem->isDummy(true)) {
            foreach ($orderItem->getChildrenItems() as $item) {
                if ($item->getIsVirtual() || $item->getLockedDoShip()) {
                    continue;
                }
                $productOptions = $item->getProductOptions();
                if (isset($productOptions['bundle_selection_attributes'])) {
                    $bundleSelectionAttributes = $this->jsonSerializer->unserialize(
                        $productOptions['bundle_selection_attributes']
                    );
                    if ($bundleSelectionAttributes) {
                        $qty = $bundleSelectionAttributes['qty'] * $shipmentItem->getQty();
                        $qty = $this->castQty($item, $qty);
                        $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                            [$item->getProductId()]
                        )[$item->getProductId()];
                        $itemsToShip[] = $this->itemToDeduct->create([
                            'sku' => $itemSku,
                            'qty' => $qty
                        ]);
                        continue;
                    }
                } else {
                    // configurable product
                    $itemsToShip[] = $this->itemToDeduct->create([
                        'sku' => $itemSku,
                        'qty' => $qty
                    ]);
                }
            }
        } else {
            $itemsToShip[] = $this->itemToDeduct->create([
                'sku' => $itemSku,
                'qty' => $qty
            ]);
        }

        return $itemsToShip;
    }

    /**
     * @param Item $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(Item $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }
}
