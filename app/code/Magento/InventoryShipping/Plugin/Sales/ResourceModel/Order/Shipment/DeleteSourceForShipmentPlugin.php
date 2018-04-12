<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\ResourceModel\Order\Shipment;

use Magento\Framework\Model\AbstractModel;
use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\DeleteShipmentSource;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;

class DeleteSourceForShipmentPlugin
{
    /**
     * @var DeleteShipmentSource
     */
    private $deleteShipmentSource;

    /**
     * @param DeleteShipmentSource $deleteShipmentSource
     */
    public function __construct(
        DeleteShipmentSource $deleteShipmentSource
    ) {
        $this->deleteShipmentSource = $deleteShipmentSource;
    }

    /**
     * @param ShipmentResource $subject
     * @param ShipmentResource $result
     * @param AbstractModel $shipment
     * @return ShipmentResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        ShipmentResource $subject,
        ShipmentResource $result,
        AbstractModel $shipment
    ) {
        $this->deleteShipmentSource->execute((int)$shipment->getId());

        return $result;
    }
}
