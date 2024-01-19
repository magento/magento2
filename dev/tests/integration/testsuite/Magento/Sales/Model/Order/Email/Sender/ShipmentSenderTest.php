<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Shipment;
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
    private const NEW_CUSTOMER_EMAIL = 'new.customer@example.com';
    private const OLD_CUSTOMER_EMAIL = 'customer@example.com';
    private const ORDER_EMAIL = 'customer@example.com';

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = Bootstrap::getObjectManager()
            ->get(CustomerRepositoryInterface::class);
    }

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
     * Test that when a customer email is modified, the shipment is sent to the new email
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoAppArea frontend
     */
    public function testSendWhenCustomerEmailWasModified()
    {
        $customer = $this->customerRepository->getById(1);
        $customer->setEmail(self::NEW_CUSTOMER_EMAIL);
        $this->customerRepository->save($customer);

        $order = $this->createOrder();
        $shipment = $this->createShipment($order);
        $shipmentIdentity = $this->createShipmentEntity();
        $shipmentSender = $this->createShipmentSender($shipmentIdentity);

        $this->assertEmpty($shipment->getEmailSent());
        $result = $shipmentSender->send($shipment, true);

        $this->assertEquals(self::NEW_CUSTOMER_EMAIL, $shipmentIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($shipment->getEmailSent());
    }

    /**
     * Test that when a customer email is not modified, the shipment is sent to the old customer email
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoAppArea frontend
     */
    public function testSendWhenCustomerEmailWasNotModified()
    {
        $order = $this->createOrder();
        $shipment = $this->createShipment($order);
        $shipmentIdentity = $this->createShipmentEntity();
        $shipmentSender = $this->createShipmentSender($shipmentIdentity);

        $this->assertEmpty($shipment->getEmailSent());
        $result = $shipmentSender->send($shipment, true);

        $this->assertEquals(self::OLD_CUSTOMER_EMAIL, $shipmentIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($shipment->getEmailSent());
    }

    /**
     * Test that when an order has not customer the shipment is sent to the order email
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoAppArea frontend
     */
    public function testSendWithoutCustomer()
    {
        $order = $this->createOrder();
        $shipment = $this->createShipment($order);

        /** @var ShipmentIdentity $shipmentIdentity */
        $shipmentIdentity = $this->createShipmentEntity();
        /** @var ShipmentSender $shipmentSender */
        $shipmentSender = $this->createShipmentSender($shipmentIdentity);

        $this->assertEmpty($shipment->getEmailSent());
        $result = $shipmentSender->send($shipment, true);

        $this->assertEquals(self::ORDER_EMAIL, $shipmentIdentity->getCustomerEmail());
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

    private function createShipment(Order $order): Shipment
    {
        $shipment = Bootstrap::getObjectManager()->create(
            Shipment::class
        );
        $shipment->setOrder($order);

        return $shipment;
    }

    private function createOrder(): Order
    {
        $order = Bootstrap::getObjectManager()
            ->create(Order::class);
        $order->loadByIncrementId('100000001');

        return $order;
    }

    private function createShipmentEntity(): ShipmentIdentity
    {
        return Bootstrap::getObjectManager()->create(
            ShipmentIdentity::class
        );
    }

    private function createShipmentSender(ShipmentIdentity $shipmentIdentity): ShipmentSender
    {
        return Bootstrap::getObjectManager()
            ->create(
                ShipmentSender::class,
                [
                    'identityContainer' => $shipmentIdentity,
                ]
            );
    }
}
