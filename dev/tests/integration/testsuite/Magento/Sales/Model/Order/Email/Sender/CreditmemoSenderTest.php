<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\TestFramework\Helper\Bootstrap;

class CreditmemoSenderTest extends \PHPUnit\Framework\TestCase
{
    const NEW_CUSTOMER_EMAIL = 'new.customer@example.com';
    const OLD_CUSTOMER_EMAIL = 'customer@null.com';
    const ORDER_EMAIL = 'customer@null.com';

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
        Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $order = Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $creditmemo = Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\Order\Creditmemo::class
        );
        $creditmemo->setOrder($order);

        $this->assertEmpty($creditmemo->getEmailSent());

        $creditmemoSender = Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order\Email\Sender\CreditmemoSender::class);
        $result = $creditmemoSender->send($creditmemo, true);

        $this->assertTrue($result);
        $this->assertNotEmpty($creditmemo->getEmailSent());
    }

    /**
     * Test that when a customer email is modified, the credit memo is sent to the new email
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
        $creditmemo = $this->createCreditmemo($order);

        $this->assertEmpty($creditmemo->getEmailSent());

        $craditmemoIdentity = $this->createCreditMemoIdentity();
        $creditmemoSender = $this->createCreditMemoSender($craditmemoIdentity);
        $result = $creditmemoSender->send($creditmemo, true);

        $this->assertEquals(self::NEW_CUSTOMER_EMAIL, $craditmemoIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($creditmemo->getEmailSent());
    }

    /**
     * Test that when a customer email is not modified, the credit memo is sent to the old customer email
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoAppArea frontend
     */
    public function testSendWhenCustomerEmailWasNotModified()
    {
        $order = $this->createOrder();
        $creditmemo = $this->createCreditmemo($order);

        $this->assertEmpty($creditmemo->getEmailSent());

        $craditmemoIdentity = $this->createCreditMemoIdentity();
        $creditmemoSender = $this->createCreditMemoSender($craditmemoIdentity);
        $result = $creditmemoSender->send($creditmemo, true);

        $this->assertEquals(self::OLD_CUSTOMER_EMAIL, $craditmemoIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($creditmemo->getEmailSent());
    }

    /**
     * Test that when an order has not customer the credit memo is sent to the order email
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoAppArea frontend
     */
    public function testSendWithoutCustomer()
    {
        $order = $this->createOrder();
        $creditmemo = $this->createCreditmemo($order);

        $this->assertEmpty($creditmemo->getEmailSent());

        $creditmemoIdentity = $this->createCreditMemoIdentity();
        $creditmemoSender = $this->createCreditMemoSender($creditmemoIdentity);
        $result = $creditmemoSender->send($creditmemo, true);

        $this->assertEquals(self::ORDER_EMAIL, $creditmemoIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($creditmemo->getEmailSent());
    }

    private function createCreditmemo(Order $order): Order\Creditmemo
    {
        $creditmemo = Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\Order\Creditmemo::class
        );
        $creditmemo->setOrder($order);
        return $creditmemo;
    }

    private function createOrder(): Order
    {
        $order = Bootstrap::getObjectManager()
            ->create(Order::class);
        $order->loadByIncrementId('100000001');

        return $order;
    }

    private function createCreditMemoIdentity(): CreditmemoIdentity
    {
        return Bootstrap::getObjectManager()->create(
            CreditmemoIdentity::class
        );
    }

    private function createCreditMemoSender(CreditmemoIdentity $creditmemoIdentity): CreditmemoSender
    {
        return Bootstrap::getObjectManager()
            ->create(
                CreditmemoSender::class,
                [
                    'identityContainer' => $creditmemoIdentity,
                ]
            );
    }
}
