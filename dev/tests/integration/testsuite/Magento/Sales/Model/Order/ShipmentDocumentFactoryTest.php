<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for shipment document factory.
 */
class ShipmentDocumentFactoryTest extends TestCase
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var ShipmentDocumentFactory
     */
    private $shipmentDocumentFactory;

    /**
     * @var ShipmentCreationArgumentsInterface
     */
    private $shipmentCreationArgumentsInterface;

    /**
     * @var ShipmentCreationArgumentsExtensionInterfaceFactory
     */
    private $shipmentCreationArgumentsExtensionInterfaceFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->order = $objectManager->create(Order::class);
        $this->shipmentDocumentFactory = $objectManager->create(ShipmentDocumentFactory::class);
        $this->shipmentCreationArgumentsInterface = $objectManager
            ->create(ShipmentCreationArgumentsInterface::class);
        $this->shipmentCreationArgumentsExtensionInterfaceFactory = $objectManager
            ->create(ShipmentCreationArgumentsExtensionInterfaceFactory::class);
    }

    /**
     * Create shipment with shipment creation arguments.
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreate(): void
    {
        $order = $this->order->loadByIncrementId('100000001');
        $argumentsExtensionAttributes = $this->shipmentCreationArgumentsExtensionInterfaceFactory->create([
            'data' => ['test_attribute_value' => 'test_value']
        ]);
        $this->shipmentCreationArgumentsInterface->setExtensionAttributes($argumentsExtensionAttributes);
        $shipment = $this->shipmentDocumentFactory->create(
            $order,
            [],
            [],
            null,
            false,
            [],
            $this->shipmentCreationArgumentsInterface
        );
        $shipmentExtensionAttributes = $shipment->getExtensionAttributes();
        self::assertEquals('test_value', $shipmentExtensionAttributes->getTestAttributeValue());
    }
}
