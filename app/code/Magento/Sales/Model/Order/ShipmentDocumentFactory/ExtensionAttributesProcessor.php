<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\ShipmentDocumentFactory;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Reflection\ExtensionAttributesProcessor as AttributesProcessor;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Build and set extension attributes for shipment.
 */
class ExtensionAttributesProcessor
{
    /**
     * @var ShipmentExtensionFactory
     */
    private $shipmentExtensionFactory;

    /**
     * @var ExtensionAttributesProcessor
     */
    private $extensionAttributesProcessor;

    /**
     * @param ShipmentExtensionFactory $shipmentExtensionFactory
     * @param AttributesProcessor $extensionAttributesProcessor
     */
    public function __construct(
        ShipmentExtensionFactory $shipmentExtensionFactory,
        AttributesProcessor $extensionAttributesProcessor
    ) {
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
        $this->extensionAttributesProcessor = $extensionAttributesProcessor;
    }

    /**
     * @param ShipmentInterface $shipment
     * @param ShipmentCreationArgumentsInterface $arguments
     * @return void
     */
    public function execute(
        ShipmentInterface $shipment,
        ShipmentCreationArgumentsInterface $arguments = null
    ): void {
        if (null === $arguments) {
            return;
        }

        $shipmentExtensionAttributes = $shipment->getExtensionAttributes();
        if (null === $shipmentExtensionAttributes) {
            $shipmentExtensionAttributes = $this->shipmentExtensionFactory->create();
        }

        $attributes = $arguments->getExtensionAttributes();
        $extensionAttributes = $this->extensionAttributesProcessor->buildOutputDataArray(
            $attributes,
            ShipmentCreationArgumentsExtensionInterface::class
        );

        foreach ($extensionAttributes as $code => $value) {
            $setMethod = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($code);

            if (method_exists($shipmentExtensionAttributes, $setMethod)) {
                $shipmentExtensionAttributes->$setMethod($value);
            }
        }
    }
}
