<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Area;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CreditmemoSenderTest extends TestCase
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
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSend()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $creditmemo = Bootstrap::getObjectManager()->create(Creditmemo::class);
        $creditmemo->setOrder($order);

        $this->assertEmpty($creditmemo->getEmailSent());

        $creditmemoSender = Bootstrap::getObjectManager()->create(CreditmemoSender::class);
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

    /**
     * Verify order will be marked send email on non default store in case default store order email sent is disabled.
     *
     * @magentoDataFixture Magento/Sales/_files/order_fixture_store.php
     * @magentoConfigFixture default/sales_email/creditmemo/enabled 0
     * @magentoConfigFixture default/sales_email/general/async_sending 1
     * @magentoConfigFixture fixturestore/sales_email/creditmemo/enabled 1
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     */
    public function testSendCreditmemeoEmailFromNonDefaultStore()
    {
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $store = $storeManager->getStore('fixturestore');
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->loadByIncrementIdAndStoreId('100000001', $store->getId());
        $order->setCustomerEmail('customer@example.com');
        $creditmemo = Bootstrap::getObjectManager()->create(Creditmemo::class);
        $creditmemo->setOrder($order);
        $creditmemoSender = Bootstrap::getObjectManager()->create(CreditmemoSender::class);
        $result = $creditmemoSender->send($creditmemo);
        $this->assertFalse($result);
        $this->assertTrue($creditmemo->getSendEmail());
    }

    private function createCreditmemo(Order $order): Creditmemo
    {
        $creditmemo = Bootstrap::getObjectManager()->create(Creditmemo::class);
        $creditmemo->setOrder($order);
        return $creditmemo;
    }

    private function createOrder(): Order
    {
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->loadByIncrementId('100000001');

        return $order;
    }

    private function createCreditMemoIdentity(): CreditmemoIdentity
    {
        return Bootstrap::getObjectManager()->create(CreditmemoIdentity::class);
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
