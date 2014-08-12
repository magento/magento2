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
namespace Magento\Paypal\Controller;

class ExpressTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Paypal/_files/quote_payment.php
     */
    public function testReviewAction()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Checkout\Model\Session'
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
        $quote = $this->_objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test02', 'reserved_order_id');
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->load('100000002', 'increment_id');
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
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
        $paypalSession = $this->_objectManager->get('Magento\Framework\Session\Generic');
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
        $fixtureCustomerFirstname = 'Firstname';
        $fixtureQuoteReserveId = 'test01';

        /** Preconditions */
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService */
        $customerService = $this->_objectManager->get('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customerData = $customerService->getCustomer($fixtureCustomerId);
        $customerSession->setCustomerDataObject($customerData);

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->_objectManager->create('Magento\Sales\Model\Quote');
        $quote->load($fixtureQuoteReserveId, 'reserved_order_id');

        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
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
        /** @var \Magento\Sales\Model\Quote $updatedQuote */
        $updatedQuote = $this->_objectManager->create('Magento\Sales\Model\Quote');
        $updatedQuote->load($fixtureQuoteReserveId, 'reserved_order_id');
        $this->assertEquals(
            $fixtureCustomerEmail,
            $updatedQuote->getCustomerEmail(),
            "Customer email in quote is invalid."
        );
        $this->assertEquals(
            $fixtureCustomerFirstname,
            $updatedQuote->getCustomerFirstname(),
            "Customer first name in quote is invalid."
        );
    }
}
