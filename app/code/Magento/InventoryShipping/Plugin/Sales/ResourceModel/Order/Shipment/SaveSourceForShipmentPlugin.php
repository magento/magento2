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
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

class SaveSourceForShipmentPlugin
{
    /**
     * @var SaveShipmentSource
     */
    private $saveShipmentSource;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param SaveShipmentSource $saveShipmentSource
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        SaveShipmentSource $saveShipmentSource,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->saveShipmentSource = $saveShipmentSource;
        $this->defaultSourceProvider = $defaultSourceProvider;
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
            $sourceCode = $this->defaultSourceProvider->getCode();
        }
        $this->saveShipmentSource->execute((int)$shipment->getId(), $sourceCode);

        return $result;
    }
}
