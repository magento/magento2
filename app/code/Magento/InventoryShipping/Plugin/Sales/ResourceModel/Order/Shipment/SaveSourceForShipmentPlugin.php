<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\ResourceModel\Order\Shipment;

use Magento\Framework\Model\AbstractModel;
use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\SaveShipmentSource;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\InventoryShipping\Model\SourceSelection\GetDefaultSourceSelectionAlgorithmCodeInterface;

class SaveSourceForShipmentPlugin
{
    /**
     * @var SaveShipmentSource
     */
    private $saveShipmentSource;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @param SaveShipmentSource $saveShipmentSource
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     */
    public function __construct(
        SaveShipmentSource $saveShipmentSource,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
    ) {
        $this->saveShipmentSource = $saveShipmentSource;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
    }

    /**
     * @param ShipmentResource $subject
     * @param ShipmentResource $result
     * @param AbstractModel $shipment
     * @return ShipmentResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        ShipmentResource $subject,
        ShipmentResource $result,
        AbstractModel $shipment
    ) {
        if (!empty($shipment->getExtensionAttributes())
            && $shipment->getExtensionAttributes()->getSourceCode()) {
            $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
        } else {
            $sourceCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
        }
        $this->saveShipmentSource->execute((int)$shipment->getId(), $sourceCode);

        return $result;
    }
}
