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

namespace Magento\Paypal\Model\Express;

use Magento\Customer\Model\Customer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Sales\Model\Quote;

class CheckoutTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManager */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Verify that an order placed with an existing customer can re-use the customer addresses.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testPrepareCustomerQuote()
    {
        /** @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService */
        $addressService = $this->_objectManager->get('Magento\Customer\Service\V1\CustomerAddressServiceInterface');
        /** @var Quote $quote */
        $quote = $this->_getFixtureQuote();
        $quote->setCheckoutMethod(Onepage::METHOD_CUSTOMER); // to dive into _prepareCustomerQuote() on switch
        $quote->getShippingAddress()->setSameAsBilling(0);
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load(1);
        $customer->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->save();

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $customerSession->loginById(1);
        $checkout = $this->_getCheckout($quote);
        $checkout->place('token');

        $this->assertEquals(1, $quote->getCustomerId());
        $this->assertEquals(2, count($addressService->getAddresses($quote->getCustomerId())));

        $this->assertEquals(1, $quote->getBillingAddress()->getCustomerAddressId());
        $this->assertEquals(2, $quote->getShippingAddress()->getCustomerAddressId());

        $order = $checkout->getOrder();
        $this->assertEquals(1, $order->getBillingAddress()->getCustomerAddressId());
        $this->assertEquals(2, $order->getShippingAddress()->getCustomerAddressId());
    }

    /**
     * Verify that an order placed with a new customer will create the customer.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testPrepareNewCustomerQuote()
    {
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService */
        $customerService = $this->_objectManager->get('Magento\Customer\Service\V1\CustomerAccountServiceInterface');

        /** @var Quote $quote */
        $quote = $this->_getFixtureQuote();

        $quote->setCheckoutMethod(Onepage::METHOD_REGISTER); // to dive into _prepareNewCustomerQuote() on switch
        $quote->setCustomerEmail('user@example.com');
        $quote->setCustomerFirstname('Firstname');
        $quote->setCustomerLastname('Lastname');
        $quote->setCustomerIsGuest(false);
        $checkout = $this->_getCheckout($quote);
        $checkout->place('token');
        $customer = $customerService->getCustomer($quote->getCustomerId());
        $customerDetails = $customerService->getCustomerDetails($customer->getId());
        $this->assertEquals('user@example.com', $customer->getEmail());
        $this->assertEquals('11111111', $customerDetails->getAddresses()[0]->getTelephone());
    }

    /**
     * Verify that an order placed with a new unconfirmed customer alerts the user that they must confirm the account.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store customer/create_account/confirm true
     */
    public function testPrepareNewCustomerQuoteConfirmationRequired()
    {
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService */
        $customerService = $this->_objectManager->get('Magento\Customer\Service\V1\CustomerAccountServiceInterface');

        /** @var Quote $quote */
        $quote = $this->_getFixtureQuote();

        $quote->setCheckoutMethod(Onepage::METHOD_REGISTER); // to dive into _prepareNewCustomerQuote() on switch
        $quote->setCustomerEmail('user@example.com');
        $quote->setCustomerFirstname('Firstname');
        $quote->setCustomerLastname('Lastname');
        $quote->setCustomerIsGuest(false);

        $checkout = $this->_getCheckout($quote);
        $checkout->place('token');
        $customer = $customerService->getCustomer($quote->getCustomerId());
        $customerDetails = $customerService->getCustomerDetails($customer->getId());
        $this->assertEquals('user@example.com', $customer->getEmail());
        $this->assertEquals('11111111', $customerDetails->getAddresses()[0]->getTelephone());

        /** @var \Magento\Framework\Message\ManagerInterface $messageManager */
        $messageManager = $this->_objectManager->get('\Magento\Framework\Message\ManagerInterface');
        $confirmationText = sprintf(
            'customer/account/confirmation/email/%s/key/',
            $customerDetails->getCustomer()->getEmail()
        );
        /** @var \Magento\Framework\Message\MessageInterface $message */
        $message = $messageManager->getMessages()->getLastAddedMessage();
        $this->assertInstanceOf('\Magento\Framework\Message\MessageInterface', $message);
        $this->assertTrue(
            strpos($message->getText(), $confirmationText) !== false
        );

    }

    /**
     * Verify that after placing the order, addresses are associated with the order and the quote is a guest quote.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testPlaceGuestQuote()
    {
        /** @var Quote $quote */
        $quote = $this->_getFixtureQuote();
        $quote->setCheckoutMethod(Onepage::METHOD_GUEST); // to dive into _prepareGuestQuote() on switch
        $quote->getShippingAddress()->setSameAsBilling(0);

        $checkout = $this->_getCheckout($quote);
        $checkout->place('token');

        $this->assertNull($quote->getCustomerId());
        $this->assertTrue($quote->getCustomerIsGuest());
        $this->assertEquals(
            \Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID,
            $quote->getCustomerGroupId()
        );

        $this->assertNotEmpty($quote->getBillingAddress());
        $this->assertNotEmpty($quote->getShippingAddress());

        $order = $checkout->getOrder();
        $this->assertNotEmpty($order->getBillingAddress());
        $this->assertNotEmpty($order->getShippingAddress());
    }

    /**
     * @param Quote $quote
     * @return Checkout
     */
    protected function _getCheckout(Quote $quote)
    {
        return $this->_objectManager->create(
            'Magento\Paypal\Model\Express\Checkout',
            [
                'params' => [
                    'config' => $this->getMock('Magento\Paypal\Model\Config', [], [], '', false),
                    'quote' => $quote,
                ]
            ]
        );
    }

    /**
     * @return Quote
     */
    protected function _getFixtureQuote()
    {
        /** @var \Magento\Sales\Model\Resource\Quote\Collection $quoteCollection */
        $quoteCollection = $this->_objectManager->create('Magento\Sales\Model\Resource\Quote\Collection');

        return $quoteCollection->getLastItem();
    }
}
