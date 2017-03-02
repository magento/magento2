<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Express;

use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Type\Onepage;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express\Checkout;
use Magento\Paypal\Model\Api\Type\Factory;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Info;

/**
 * Class CheckoutTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var Info
     */
    private $paypalInfo;

    /**
     * @var Config
     */
    private $paypalConfig;

    /**
     * @var Factory
     */
    private $apiTypeFactory;

    /**
     * @var Nvp
     */
    private $api;

    /**
     * @var Checkout
     */
    private $checkoutModel;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_objectManager = Bootstrap::getObjectManager();

        $this->paypalInfo = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paypalConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->api = $this->getMockBuilder(Nvp::class)
            ->disableOriginalConstructor()
            ->setMethods(['call', 'getExportedShippingAddress', 'getExportedBillingAddress'])
            ->getMock();

        $this->api->expects($this->any())
            ->method('call')
            ->will($this->returnValue([]));

        $this->apiTypeFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiTypeFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->api));
    }

    /**
     * Verify that an order placed with an existing customer can re-use the customer addresses.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testPrepareCustomerQuote()
    {
        /** @var Quote $quote */
        $quote = $this->_getFixtureQuote();
        $quote->setCheckoutMethod(Onepage::METHOD_CUSTOMER); // to dive into _prepareCustomerQuote() on switch
        $quote->getShippingAddress()->setSameAsBilling(0);
        $quote->setReservedOrderId(null);
        $customer = $this->_objectManager->create(\Magento\Customer\Model\Customer::class)->load(1);
        $customer->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->save();

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);
        $customerSession->loginById(1);
        $checkout = $this->_getCheckout($quote);
        $checkout->place('token');

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerService */
        $customerService = $this->_objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerService->getById($quote->getCustomerId());

        $this->assertEquals(1, $quote->getCustomerId());
        $this->assertEquals(2, count($customer->getAddresses()));

        $this->assertEquals(1, $quote->getBillingAddress()->getCustomerAddressId());
        $this->assertEquals(2, $quote->getShippingAddress()->getCustomerAddressId());

        $order = $checkout->getOrder();
        $this->assertEquals(1, $order->getBillingAddress()->getCustomerAddressId());
        $this->assertEquals(2, $order->getShippingAddress()->getCustomerAddressId());
    }

    /**
     * Verify that after placing the order, addresses are associated with the order and the quote is a guest quote.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_express.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testPlaceGuestQuote()
    {
        /** @var Quote $quote */
        $quote = $this->_getFixtureQuote();
        $quote->setCheckoutMethod(Onepage::METHOD_GUEST); // to dive into _prepareGuestQuote() on switch
        $quote->getShippingAddress()->setSameAsBilling(0);
        $quote->setReservedOrderId(null);

        $checkout = $this->_getCheckout($quote);
        $checkout->place('token');

        $this->assertNull($quote->getCustomerId());
        $this->assertTrue($quote->getCustomerIsGuest());
        $this->assertEquals(
            \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
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
            Checkout::class,
            [
                'params' => [
                    'config' => $this->getMock(\Magento\Paypal\Model\Config::class, [], [], '', false),
                    'quote' => $quote,
                ]
            ]
        );
    }

    /**
     * Verify that an order placed with an existing customer can re-use the customer addresses.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReturnFromPaypal()
    {
        $quote = $this->_getFixtureQuote();
        $this->checkoutModel = $this->_objectManager->create(
            Checkout::class,
            [
                'params' => ['quote' => $quote, 'config' => $this->paypalConfig],
                'apiTypeFactory' => $this->apiTypeFactory,
                'paypalInfo' => $this->paypalInfo
            ]
        );

        $exportedBillingAddress = $this->_getExportedAddressFixture($quote->getBillingAddress()->getData());
        $this->api->expects($this->any())
            ->method('getExportedBillingAddress')
            ->will($this->returnValue($exportedBillingAddress));

        $exportedShippingAddress = $this->_getExportedAddressFixture($quote->getShippingAddress()->getData());
        $this->api->expects($this->any())
            ->method('getExportedShippingAddress')
            ->will($this->returnValue($exportedShippingAddress));

        $this->paypalInfo->expects($this->once())->method('importToPayment')->with($this->api, $quote->getPayment());

        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 1);

        $this->checkoutModel->returnFromPaypal('token');

        $billingAddress = $quote->getBillingAddress();

        $this->assertContains('exported', $billingAddress->getFirstname());
        $this->assertEquals('note', $billingAddress->getCustomerNote());

        $shippingAddress = $quote->getShippingAddress();
        $this->assertTrue((bool)$shippingAddress->getSameAsBilling());
        $this->assertNull($shippingAddress->getPrefix());
        $this->assertNull($shippingAddress->getMiddlename());
        $this->assertNull($shippingAddress->getLastname());
        $this->assertNull($shippingAddress->getSuffix());
        $this->assertTrue($shippingAddress->getShouldIgnoreValidation());
        $this->assertContains('exported', $shippingAddress->getFirstname());
        $paymentAdditionalInformation = $quote->getPayment()->getAdditionalInformation();
        $this->assertArrayHasKey(Checkout::PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD, $paymentAdditionalInformation);
        $this->assertArrayHasKey(Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID, $paymentAdditionalInformation);
        $this->assertArrayHasKey(Checkout::PAYMENT_INFO_TRANSPORT_TOKEN, $paymentAdditionalInformation);
        $this->assertTrue($quote->getPayment()->hasMethod());
        $this->assertTrue($quote->getTotalsCollectedFlag());
    }

    /**
     * The case when handling address data from Paypal button.
     * System's address fields are replacing from export Paypal data.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReturnFromPaypalButton()
    {
        $quote = $this->_getFixtureQuote();
        $this->prepareCheckoutModel($quote);
        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 1);

        $this->checkoutModel->returnFromPaypal('token');

        $shippingAddress = $quote->getShippingAddress();

        $prefix = 'exported';
        $this->assertEquals([$prefix .$this->getExportedData()['street']], $shippingAddress->getStreet());
        $this->assertEquals($prefix . $this->getExportedData()['firstname'], $shippingAddress->getFirstname());
        $this->assertEquals($prefix . $this->getExportedData()['city'], $shippingAddress->getCity());
        $this->assertEquals($prefix . $this->getExportedData()['telephone'], $shippingAddress->getTelephone());
        // This fields not in exported keys list. Fields the same as quote shipping and billing address.
        $this->assertNotEquals($prefix . $this->getExportedData()['region'], $shippingAddress->getRegion());
        $this->assertNotEquals($prefix . $this->getExportedData()['email'], $shippingAddress->getEmail());
    }

    /**
     * The case when handling address data from the checkout.
     * System's address fields are not replacing from export Paypal data.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReturnFromPaypalIfCheckout()
    {
        $quote = $this->_getFixtureQuote();
        $this->prepareCheckoutModel($quote);
        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 0);

        $this->checkoutModel->returnFromPaypal('token');

        $shippingAddress = $quote->getShippingAddress();

        $prefix = 'exported';

        $this->assertNotEquals([$prefix .$this->getExportedData()['street']], $shippingAddress->getStreet());
        $this->assertNotEquals($prefix . $this->getExportedData()['firstname'], $shippingAddress->getFirstname());
        $this->assertNotEquals($prefix . $this->getExportedData()['city'], $shippingAddress->getCity());
        $this->assertNotEquals($prefix . $this->getExportedData()['telephone'], $shippingAddress->getTelephone());
    }

    /**
     * Initialize a checkout model mock.
     *
     * @param Quote $quote
     */
    private function prepareCheckoutModel(Quote $quote)
    {
        $this->checkoutModel = $this->_objectManager->create(
            Checkout::class,
            [
                'params'         => ['quote' => $quote, 'config' => $this->paypalConfig],
                'apiTypeFactory' => $this->apiTypeFactory,
                'paypalInfo'     => $this->paypalInfo
            ]
        );

        $exportedBillingAddress = $this->_getExportedAddressFixture($this->getExportedData());
        $this->api->expects($this->any())
            ->method('getExportedBillingAddress')
            ->will($this->returnValue($exportedBillingAddress));

        $exportedShippingAddress = $this->_getExportedAddressFixture($this->getExportedData());
        $this->api->expects($this->any())
            ->method('getExportedShippingAddress')
            ->will($this->returnValue($exportedShippingAddress));

        $this->paypalInfo->expects($this->once())
            ->method('importToPayment')
            ->with($this->api, $quote->getPayment());
    }

    /**
     * A Paypal response stub.
     *
     * @return array
     */
    private function getExportedData()
    {
        return
            [
                'company'    => 'Testcompany',
                'email'      => 'buyeraccountmpi@gmail.com',
                'firstname'  => 'testFirstName',
                'country_id' => 'US',
                'region'     => 'testRegion',
                'city'       => 'testSity',
                'street'     => 'testStreet',
                'telephone'  => '223344',
            ];
    }

    /**
     * Prepare fixture for exported address
     *
     * @param array $addressData
     * @return \Magento\Framework\DataObject
     */
    protected function _getExportedAddressFixture(array $addressData)
    {
        $addressDataKeys = ['firstname', 'lastname', 'street', 'city', 'telephone'];
        $result = [];
        foreach ($addressDataKeys as $key) {
            if (isset($addressData[$key])) {
                $result[$key] = 'exported' . $addressData[$key];
            }
        }
        $fixture = new \Magento\Framework\DataObject($result);
        $fixture->setExportedKeys($addressDataKeys);
        $fixture->setData('note', 'note');
        return $fixture;
    }

    /**
     * @return Quote
     */
    protected function _getFixtureQuote()
    {
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteCollection */
        $quoteCollection = $this->_objectManager->create(\Magento\Quote\Model\ResourceModel\Quote\Collection::class);

        return $quoteCollection->getLastItem();
    }
}
