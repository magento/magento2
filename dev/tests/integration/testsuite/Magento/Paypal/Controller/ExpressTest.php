<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller;

class ExpressTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Paypal/_files/quote_payment.php
     */
    public function testReviewAction()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Checkout\Model\Session::class
        )->setQuoteId(
            $quote->getId()
        );

        $this->dispatch('paypal/express/review');

        $html = $this->getResponse()->getBody();
        $this->assertContains('Simple Product', $html);
        $this->assertContains('Review', $html);
        $this->assertContains('/paypal/express/placeOrder/', $html);
    }

    /**
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express.php
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     */
    public function testCancelAction()
    {
        $quote = $this->_objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('100000002', 'reserved_order_id');
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class);
        $order->load('100000002', 'increment_id');
        $session = $this->_objectManager->get(\Magento\Checkout\Model\Session::class);
        $session->setLoadInactive(true);
        $session->setLastRealOrderId(
            $order->getRealOrderId()
        )->setLastOrderId(
            $order->getId()
        )->setLastQuoteId(
            $order->getQuoteId()
        )->setQuoteId(
            $order->getQuoteId()
        );
        /** @var $paypalSession \Magento\Framework\Session\Generic */
        $paypalSession = $this->_objectManager->get(\Magento\Paypal\Model\Session::class);
        $paypalSession->setExpressCheckoutToken('token');

        $this->dispatch('paypal/express/cancel');

        $order->load('100000002', 'increment_id');
        $this->assertEquals('canceled', $order->getState());
        $this->assertEquals($session->getQuote()->getGrandTotal(), $quote->getGrandTotal());
        $this->assertEquals($session->getQuote()->getItemsCount(), $quote->getItemsCount());
    }

    /**
     * Test ensures only that customer data was copied to quote correctly.
     *
     * Note that test does not verify communication during remote calls to PayPal.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testStartActionCustomerToQuote()
    {
        $fixtureCustomerId = 1;
        $fixtureCustomerEmail = 'customer@example.com';
        $fixtureCustomerFirstname = 'John';
        $fixtureQuoteReserveId = 'test01';

        /** Preconditions */
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->_objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customerData = $customerRepository->getById($fixtureCustomerId);
        $customerSession->setCustomerDataObject($customerData);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load($fixtureQuoteReserveId, 'reserved_order_id');

        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = $this->_objectManager->get(\Magento\Checkout\Model\Session::class);
        $checkoutSession->setQuoteId($quote->getId());

        /** Preconditions check */
        $this->assertNotEquals(
            $fixtureCustomerEmail,
            $quote->getCustomerEmail(),
            "Precondition failed: customer email in quote is invalid."
        );
        $this->assertNotEquals(
            $fixtureCustomerFirstname,
            $quote->getCustomerFirstname(),
            "Precondition failed: customer first name in quote is invalid."
        );

        /** Execute SUT */
        $this->dispatch('paypal/express/start');

        /** Check if customer data was copied to quote correctly */
        /** @var \Magento\Quote\Model\Quote $updatedQuote */
        $updatedQuote = $this->_objectManager->create(\Magento\Quote\Model\Quote::class);
        $updatedQuote->load($fixtureQuoteReserveId, 'reserved_order_id');
        $this->assertEquals(
            $fixtureCustomerEmail,
            $updatedQuote->getCustomer()->getEmail(),
            "Customer email in quote is invalid."
        );
        $this->assertEquals(
            $fixtureCustomerFirstname,
            $updatedQuote->getCustomer()->getFirstname(),
            "Customer first name in quote is invalid."
        );
    }
}
