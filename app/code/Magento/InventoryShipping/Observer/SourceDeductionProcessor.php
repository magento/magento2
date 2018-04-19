<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventoryShipping\Model\SourceDeduction\SourceDeductionServiceInterface;
use Magento\InventoryShipping\Model\SourceDeduction\Request\SourceDeductionRequestInterfaceFactory;
use Magento\InventoryCatalog\Model\DefaultSourceProvider;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * Class SourceDeductionProcessor
 */
class SourceDeductionProcessor implements ObserverInterface
{
    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

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
     * @var GetItemsToDeduct
     */
    private $getItemsToDeduct;

    /**
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory
     * @param SourceDeductionServiceInterface $sourceDeductionService
     * @param DefaultSourceProvider $defaultSourceProvider
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param GetItemsToDeduct $getItemsToDeduct
     */
    public function __construct(
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory,
        SourceDeductionServiceInterface $sourceDeductionService,
        DefaultSourceProvider $defaultSourceProvider,
        SalesEventInterfaceFactory $salesEventFactory,
        IsSingleSourceModeInterface $isSingleSourceMode,
        GetItemsToDeduct $getItemsToDeduct
    ) {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->salesEventFactory = $salesEventFactory;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->getItemsToDeduct = $getItemsToDeduct;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\InputException
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
            'items' => $this->getItemsToDeduct->execute($shipmentItem),
            'salesEvent' => $salesEvent
        ]);
        $this->sourceDeductionService->execute($sourceDeductionRequest);
    }
}
