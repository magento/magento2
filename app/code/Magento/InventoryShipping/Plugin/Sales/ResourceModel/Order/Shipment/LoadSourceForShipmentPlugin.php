<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\ResourceModel\Order\Shipment;

use Magento\Framework\Model\AbstractModel;
use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\GetSourceCodeByShipmentId;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;

class LoadSourceForShipmentPlugin
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
     * @param ShipmentResource $subject
     * @param ShipmentResource $result
     * @param AbstractModel $shipment
     * @return ShipmentResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(
        ShipmentResource $subject,
        ShipmentResource $result,
        AbstractModel $shipment
    ) {
        $shipmentExtension = $shipment->getExtensionAttributes();
        if (empty($shipmentExtension)) {
            $shipmentExtension = $this->shipmentExtensionFactory->create();
        }
        $sourceCode = $this->getSourceCodeByShipmentId->execute((int)$shipment->getId());
        $shipmentExtension->setSourceCode($sourceCode);
        $shipment->setExtensionAttributes($shipmentExtension);

        return $result;
    }
}
