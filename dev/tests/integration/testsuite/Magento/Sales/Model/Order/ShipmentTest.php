<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Class ShipmentTest
 * @magentoAppIsolation enabled
 * @package Magento\Sales\Model\Order
 */
class ShipmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check the correctness and stability of set/get packages of shipment
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testPackages()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $payment = $order->getPayment();
        $paymentInfoBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Payment\Helper\Data::class
        )->getInfoBlock(
            $payment
        );
        $payment->setBlockMock($paymentInfoBlock);

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $objectManager->create(\Magento\Sales\Model\Order\Shipment::class);
        $shipment->setOrder($order);

        $packages = [['1'], ['2']];

        $shipment->addItem($objectManager->create(\Magento\Sales\Model\Order\Shipment\Item::class));
        $shipment->setPackages($packages);
        $this->assertEquals($packages, $shipment->getPackages());
        $shipment->save();
        $shipment->save();
        $shipment->load($shipment->getId());
        $this->assertEquals($packages, $shipment->getPackages());
    }
}
