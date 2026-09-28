<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Controller;

use Magento\Checkout\Model\Session;
use Magento\Framework\Session\Generic as GenericSession;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Api\Type\Factory as ApiFactory;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests of Paypal Express actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpressTest extends \Magento\TestFramework\TestCase\AbstractController
{
    use MockCreationTrait;
    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Paypal/_files/quote_payment.php
     */
    public function testReviewAction()
    {
        $quote = Bootstrap::getObjectManager()->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');
        Bootstrap::getObjectManager()->get(
            Session::class
        )->setQuoteId(
            $quote->getId()
        );

        $this->dispatch('paypal/express/review');

        $html = $this->getResponse()->getBody();
        $this->assertStringContainsString('Simple Product', $html);
        $this->assertStringContainsString('Review', $html);
        $this->assertStringContainsString('/paypal/express/placeOrder/', $html);
    }

    /**
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express.php
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     */
    public function testCancelAction()
    {
        $quote = $this->_objectManager->create(Quote::class);
        $quote->load('100000002', 'reserved_order_id');
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class);
        $order->load('100000002', 'increment_id');
        $session = $this->_objectManager->get(Session::class);
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
        /** @var GenericSession $paypalSession */
        $paypalSession = $this->_objectManager->create(
            GenericSession::class,
            ['sessionNamespace' => 'paypal']
        );
        $paypalSession->setExpressCheckoutToken('token');
        $this->_objectManager->addSharedInstance($paypalSession, 'Magento\Paypal\Model\Session');

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

        /** @var Quote $quote */
        $quote = $this->_objectManager->create(Quote::class);
        $quote->load($fixtureQuoteReserveId, 'reserved_order_id');

        /** @var Session $checkoutSession */
        $checkoutSession = $this->_objectManager->get(Session::class);
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
        /** @var Quote $updatedQuote */
        $updatedQuote = $this->_objectManager->create(Quote::class);
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

    /**
     * Test return action with configurable product.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_express_configurable.php
     */
    public function testReturnAction()
    {
        $quote = $this->_objectManager->create(Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');

        $payment = $quote->getPayment();
        $payment->setMethod(\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS)
            ->setAdditionalInformation(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID, 123);

        $quote->save();

        $this->_objectManager->removeSharedInstance(Session::class);
        $session = $this->_objectManager->get(Session::class);
        $session->setQuoteId($quote->getId());

        $nvpMethods = [
            'setToken',
            'setPayerId',
            'setAmount',
            'setPaymentAction',
            'setNotifyUrl',
            'setInvNum',
            'setCurrencyCode',
            'setPaypalCart',
            'setIsLineItemsEnabled',
            'setAddress',
            'setBillingAddress',
            'callDoExpressCheckoutPayment',
            'callGetExpressCheckoutDetails',
            'getExportedBillingAddress'
        ];

        $nvpMock = $this->createPartialMockWithReflection(
            Nvp::class,
            [
                'setPaypalCart',
                'callDoExpressCheckoutPayment',
                'callGetExpressCheckoutDetails',
                'setToken',
                'setPayerId',
                'setAmount',
                'setPaymentAction',
                'setNotifyUrl',
                'setInvNum',
                'setCurrencyCode',
                'setIsLineItemsEnabled',
                'setAddress',
                'setBillingAddress',
                'getExportedBillingAddress'
            ]
        );

        $nvpMock->method('setPaypalCart')->willReturnSelf();
        $nvpMock->method('callDoExpressCheckoutPayment')->willReturnSelf();
        $nvpMock->method('callGetExpressCheckoutDetails')->willReturnSelf();
        $nvpMock->method('setToken')->willReturnSelf();
        $nvpMock->method('setPayerId')->willReturnSelf();
        $nvpMock->method('setAmount')->willReturnSelf();
        $nvpMock->method('setPaymentAction')->willReturnSelf();
        $nvpMock->method('setNotifyUrl')->willReturnSelf();
        $nvpMock->method('setInvNum')->willReturnSelf();
        $nvpMock->method('setCurrencyCode')->willReturnSelf();
        $nvpMock->method('setIsLineItemsEnabled')->willReturnSelf();
        $nvpMock->method('setAddress')->willReturnSelf();
        $nvpMock->method('setBillingAddress')->willReturnSelf();
        $nvpMock->method('getExportedBillingAddress')->willReturnSelf();

        $apiFactoryMock = $this->getMockBuilder(ApiFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $apiFactoryMock->method('create')
            ->with(Nvp::class)
            ->willReturn($nvpMock);

        $this->_objectManager->addSharedInstance($apiFactoryMock, ApiFactory::class);

        /** @var GenericSession $paypalSession */
        $paypalSession = $this->_objectManager->create(
            GenericSession::class,
            ['sessionNamespace' => 'paypal']
        );
        $paypalSession->setExpressCheckoutToken('token');
        $this->_objectManager->addSharedInstance($paypalSession, 'Magento\Paypal\Model\Session');

        $this->dispatch('paypal/express/returnAction');
        $this->assertRedirect($this->stringContains('checkout/onepage/success'));

        $this->_objectManager->removeSharedInstance(ApiFactory::class);
    }
}
