<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\Shipment;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;

class AssignSourceCodeToShipmentPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ShipmentExtensionFactory
     */
    private $shipmentExtensionFactory;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * AssignSourceCodeToShipmentPlugin constructor.
     * @param RequestInterface $request
     * @param ShipmentExtensionFactory $shipmentExtensionFactory
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     */
    public function __construct(
        RequestInterface $request,
        ShipmentExtensionFactory $shipmentExtensionFactory,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
    ) {
        $this->request = $request;
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
    }

    /**
     * @param ShipmentFactory $subject
     * @param ShipmentInterface $shipment
     * @param Order $order
     * @return ShipmentInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(ShipmentFactory $subject, ShipmentInterface $shipment, Order $order)
    {
        $sourceCode = $this->request->getParam('sourceCode');
        if (empty($sourceCode)) {
            $websiteId = $order->getStore()->getWebsiteId();
            $stockId = $this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();
            $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute((int)$stockId);
            //TODO: need ro rebuild this logic | create separate service
            if (!empty($sources) && count($sources) == 1) {
                $sourceCode = $sources[0]->getSourceCode();
            }
        }
        $shipmentExtension = $shipment->getExtensionAttributes();

        if (empty($shipmentExtension)) {
            $shipmentExtension = $this->shipmentExtensionFactory->create();
        }
        $shipmentExtension->setSourceCode($sourceCode);
        $shipment->setExtensionAttributes($shipmentExtension);

        return $shipment;
    }
}
