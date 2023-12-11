<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\ShipmentDocumentFactory;

use Magento\Framework\Reflection\ExtensionAttributesProcessor as AttributesProcessor;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentExtensionInterface;
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
        ShipmentCreationArgumentsInterface $arguments
    ): void {
        $shipmentExtensionAttributes = [];
        if (null !== $shipment->getExtensionAttributes()) {
            $shipmentExtensionAttributes = $this->extensionAttributesProcessor->buildOutputDataArray(
                $shipment->getExtensionAttributes(),
                ShipmentExtensionInterface::class
            );
        }
        $argumentsExtensionAttributes = [];
        if (null !== $arguments->getExtensionAttributes()) {
            $argumentsExtensionAttributes = $this->extensionAttributesProcessor->buildOutputDataArray(
                $arguments->getExtensionAttributes(),
                ShipmentCreationArgumentsExtensionInterface::class
            );
        }

        $mergedExtensionAttributes = $this->shipmentExtensionFactory->create([
            'data' => array_merge($shipmentExtensionAttributes, $argumentsExtensionAttributes)
        ]);

        $shipment->setExtensionAttributes($mergedExtensionAttributes);
    }
}
