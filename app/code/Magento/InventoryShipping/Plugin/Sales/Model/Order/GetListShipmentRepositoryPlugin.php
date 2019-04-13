<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\Model\Order;

use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\GetSourceCodeByShipmentId;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Add Source Information to shipments loaded with Magento\Sales\Api\ShipmentRepositoryInterface::getList
 */
class GetListShipmentRepositoryPlugin
{
    /**
     * @var ShipmentExtensionFactory
     */
    private $shipmentExtensionFactory;

    /**
     * @var GetSourceCodeByShipmentId
     */
    private $getSourceCodeByShipmentId;

    /**
     * @param ShipmentExtensionFactory $shipmentExtensionFactory
     * @param GetSourceCodeByShipmentId $getSourceCodeByShipmentId
     */
    public function __construct(
        ShipmentExtensionFactory $shipmentExtensionFactory,
        GetSourceCodeByShipmentId $getSourceCodeByShipmentId
    ) {
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
        $this->getSourceCodeByShipmentId = $getSourceCodeByShipmentId;
    }

    /**
     * Add Source Information to shipments.
     *
     * @param ShipmentRepositoryInterface $subject
     * @param ShipmentInterface[] $searchResult
     * @return ShipmentInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(ShipmentRepositoryInterface $subject, $searchResult)
    {
        /** @var ShipmentInterface $shipment */
        foreach ($searchResult->getItems() as $shipment) {
            $shipmentExtension = $shipment->getExtensionAttributes();
            if (empty($shipmentExtension)) {
                $shipmentExtension = $this->shipmentExtensionFactory->create();
            }
            $sourceCode = $this->getSourceCodeByShipmentId->execute((int)$shipment->getId());
            $shipmentExtension->setSourceCode($sourceCode);
            $shipment->setExtensionAttributes($shipmentExtension);
        }

        return $searchResult;
    }
}
