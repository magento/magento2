<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea frontend
 *
 * @deprecated since ShipmentSender is deprecated
 * @see \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
 */
class ShipmentSenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSend()
    {
        Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $order = Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $shipment = Bootstrap::getObjectManager()->get(ShipmentFactory::class)->create($order);

        $this->assertEmpty($shipment->getEmailSent());

        $orderSender = Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order\Email\Sender\ShipmentSender::class);
        $result = $orderSender->send($shipment, true);

        $this->assertTrue($result);

        $this->assertNotEmpty($shipment->getEmailSent());
    }

    /**
     * Check the correctness and stability of set/get packages of shipment
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testPackages()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $objectManager->get(ShipmentFactory::class)->create($order, $items);
        $packages = [['1'], ['2']];
        $shipment->setPackages($packages);
        $this->assertEquals($packages, $shipment->getPackages());
        $shipment->save();
        $shipment->save();
        $shipment->load($shipment->getId());
        $this->assertEquals($packages, $shipment->getPackages());
    }
}
