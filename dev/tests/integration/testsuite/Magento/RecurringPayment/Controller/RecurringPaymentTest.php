<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\RecurringPayment\Controller;

use Magento\RecurringPayment\Controller\Adminhtml\RecurringPayment;

/**
 * @magentoAppArea frontend
 */
class RecurringPaymentTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Test if recurring payment is shown on the recurring payments grid (case with no recurring payments)
     *
     * This test is appropriate for dispatch() testing since it is executed implicitly.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testPaymentsGridNoPayments()
    {
        /** Preconditions */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $customerId = 1;
        $customerSession->setCustomerId($customerId);
        $coreRegistry = $objectManager->get('Magento\Framework\Registry');
        $this->assertNotEquals(
            $customerId,
            $coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID),
            "Precondition failed: customer ID should have not been set to registry."
        );

        /** Execute SUT: dispatch() will be executed during any action invocation */
        $this->dispatch('sales/recurringPayment/index');

        /** Ensure that customer ID was set to the registry correctly */
        $this->assertEquals(
            $customerId,
            $coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID),
            "Customer ID should have been set to registry."
        );
        /** Ensure that customer ID was set to the registry correctly */
        $this->assertContains(
            'There are no recurring payments yet',
            $this->getResponse()->getBody(),
            "No recurring payments are expected to be shown for current customer."
        );
    }

    /**
     * Test if recurring payment is shown on the recurring payments grid (case with recurring payment)
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/RecurringPayment/_files/recurring_payment.php
     */
    public function testPaymentsGridWithPayments()
    {
        /** Preconditions */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $customerId = 1;
        $customerSession->setCustomerId($customerId);

        /** Execute SUT */
        $this->dispatch('sales/recurringPayment/index');

        /** Ensure that customer ID was set to the registry correctly */
        $fixturePaymentExternalReferenceId = 'external-reference-1';
        $this->assertContains(
            $fixturePaymentExternalReferenceId,
            $this->getResponse()->getBody(),
            "Fixture recurring payment is expected to be shown for current customer."
        );
    }

    /**
     * Test if orders related to recurring profile are filtered correctly by current customer (which has orders)
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testOrdersActionFilterByCustomerWithOrders()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** Preconditions */
        $fixtureCustomerId = 1;
        /** Set filter by customer */
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $customerSession->setCustomerId($fixtureCustomerId);

        /** Add relation between order and recurring payment */
        $fixtureOrderIncrementId = '100000001';
        /** @var \Magento\Sales\Model\Order $order */
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($fixtureOrderIncrementId, 'increment_id');
        /** @var \Magento\RecurringPayment\Model\Payment $payment */
        $payment = $this->_createRecurringPayment($objectManager);
        $payment->addOrderRelation($order->getId());
        $order->save();

        $this->getRequest()->setParam(RecurringPayment::PARAM_PAYMENT, $payment->getId());

        /** Execute SUT */
        $this->dispatch('sales/recurringPayment/orders');

        /** Ensure that order related to recurring payment is shown on the grid */
        $this->assertContains(
            'Orders Based on This Payment',
            $this->getResponse()->getBody(),
            'Orders related to current recurring payment for current customer are missing.'
        );
        $this->assertContains(
            $fixtureOrderIncrementId,
            $this->getResponse()->getBody(),
            'Order related to current recurring payment for current customer is missing.'
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testViewNotCustomersOwnRecurringProfile()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $customerSession->setCustomerId($this->_createSecondCustomer()->getId());

        /** Add relation between order and recurring payment */
        $fixtureOrderIncrementId = '100000001';
        /** @var \Magento\Sales\Model\Order $order */
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($fixtureOrderIncrementId, 'increment_id');
        /** @var \Magento\RecurringPayment\Model\Payment $payment */
        $payment = $this->_createRecurringPayment($objectManager);
        $payment->addOrderRelation($order->getId());
        $order->save();

        $this->getRequest()->setParam(RecurringPayment::PARAM_PAYMENT, $payment->getId());
        $this->dispatch('sales/recurringPayment/view');

        /** @var \Magento\Framework\Message\Manager $messageManager */
        $messageManager = $this->_objectManager->get('Magento\Framework\Message\Manager');
        $this->assertEquals(
            __('We can\'t find the payment you specified.'),
            $messageManager->getMessages()->getErrors()[0]->getText()
        );
    }

    /**
     * Create new recurring payment (quote and customer should be created in fixtures).
     *
     * Note that magentoDbIsolation should be used in tests which execute this method.
     *
     * @return \Magento\RecurringPayment\Model\Payment
     */
    protected function _createRecurringPayment()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $payment = $objectManager->create('Magento\RecurringPayment\Model\Payment');
        $payment
            ->setQuote($objectManager->create('Magento\Sales\Model\Quote')->load(1))
            ->setPeriodUnit('year')
            ->setPeriodFrequency(1)
            ->setScheduleDescription('Test Schedule')
            ->setBillingAmount(1)
            ->setCurrencyCode('USD')
            ->setMethodCode('paypal_express')
            ->setInternalReferenceId('rp-1')
            ->setReferenceId('external-reference-1')
            ->setCustomerId(1)
            ->save();
        $this->assertNotEmpty($payment->getId(), 'Precondition failed: payment was not created successfully.');
        return $payment;
    }

    /**
     * Create second customer.
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function _createSecondCustomer()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $customer = $objectManager->create('Magento\Customer\Model\Customer');
        $customer
            ->setWebsiteId(1)
            ->setEntityId(2)
            ->setEntityTypeId(1)
            ->setAttributeSetId(0)
            ->setEmail('customer_two@example.com')
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setDefaultBilling(1)
            ->setDefaultShipping(1);
        $customer->isObjectNew(true);
        $customer->save();
        return $customer;
    }
}
