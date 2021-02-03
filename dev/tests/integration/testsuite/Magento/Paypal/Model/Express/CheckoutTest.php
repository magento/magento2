<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Express;

use Magento\Checkout\Model\Type\Onepage;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Api\Type\Factory;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Info;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Info|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paypalInfo;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paypalConfig;

    /**
     * @var Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiTypeFactory;

    /**
     * @var Nvp|\PHPUnit\Framework\MockObject\MockObject
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
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->paypalInfo = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paypalConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->api = $this->getMockBuilder(Nvp::class)
            ->disableOriginalConstructor()
            ->setMethods(['call', 'getExportedShippingAddress', 'getExportedBillingAddress', 'getShippingRateCode'])
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
     * Verify that api has set customer email.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_express.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCheckoutStartWithBillingAddress()
    {
        $quote = $this->getFixtureQuote();
        $paypalConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiTypeFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $paypalInfo = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutModel = $this->objectManager->create(
            Checkout::class,
            [
                'params' => ['quote' => $quote, 'config' => $paypalConfig],
                'apiTypeFactory' => $apiTypeFactory,
                'paypalInfo' => $paypalInfo
            ]
        );

        $api = $this->getMockBuilder(Nvp::class)
            ->disableOriginalConstructor()
            ->setMethods(['callSetExpressCheckout'])
            ->getMock();

        $api->expects($this->any())
            ->method('callSetExpressCheckout')
            ->will($this->returnValue(null));

        $apiTypeFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($api));

        $checkoutModel->start(
            'return',
            'cancel',
            false
        );

        $this->assertEquals('test@com.com', $api->getBillingAddress()->getEmail());
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
        $quote = $this->getFixtureQuote();
        $quote->setCheckoutMethod(Onepage::METHOD_CUSTOMER); // to dive into _prepareCustomerQuote() on switch
        $quote->getShippingAddress()->setSameAsBilling(0);
        $quote->setReservedOrderId(null);
        $customer = $this->objectManager->create(\Magento\Customer\Model\Customer::class)->load(1);
        $customer->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->save();

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->objectManager->get(\Magento\Customer\Model\Session::class);
        $customerSession->loginById(1);
        $checkout = $this->getCheckout($quote);
        $checkout->place('token');

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerService */
        $customerService = $this->objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
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
        $quote = $this->getFixtureQuote();
        $quote->setCheckoutMethod(Onepage::METHOD_GUEST); // to dive into _prepareGuestQuote() on switch
        $quote->getShippingAddress()->setSameAsBilling(0);
        $quote->setReservedOrderId(null);

        $checkout = $this->getCheckout($quote);
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
    protected function getCheckout(Quote $quote)
    {
        return $this->objectManager->create(
            Checkout::class,
            [
                'params' => [
                    'config' => $this->getMockBuilder(Config::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
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
        $quote = $this->getFixtureQuote();
        $this->checkoutModel = $this->objectManager->create(
            Checkout::class,
            [
                'params' => ['quote' => $quote, 'config' => $this->paypalConfig],
                'apiTypeFactory' => $this->apiTypeFactory,
                'paypalInfo' => $this->paypalInfo
            ]
        );

        $prefix = 'exported';
        $exportedBillingAddress = $this->getExportedAddressFixture($quote->getBillingAddress()->getData(), $prefix);
        $this->api->expects($this->any())
            ->method('getExportedBillingAddress')
            ->will($this->returnValue($exportedBillingAddress));

        $exportedShippingAddress = $this->getExportedAddressFixture($quote->getShippingAddress()->getData(), $prefix);
        $this->api->expects($this->any())
            ->method('getExportedShippingAddress')
            ->will($this->returnValue($exportedShippingAddress));

        $this->paypalInfo->expects($this->once())->method('importToPayment')->with($this->api, $quote->getPayment());

        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 1);

        $this->checkoutModel->returnFromPaypal('token');

        $billingAddress = $quote->getBillingAddress();
        $this->assertStringContainsString($prefix, $billingAddress->getFirstname());
        $this->assertEquals('note', $billingAddress->getCustomerNote());

        $shippingAddress = $quote->getShippingAddress();
        $this->assertTrue((bool)$shippingAddress->getSameAsBilling());
        $this->assertNull($shippingAddress->getPrefix());
        $this->assertNull($shippingAddress->getMiddlename());
        $this->assertNull($shippingAddress->getSuffix());
        $this->assertTrue($shippingAddress->getShouldIgnoreValidation());
        $this->assertStringContainsString('exported', $shippingAddress->getFirstname());
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
     * Billing and Shipping address are the same
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReturnFromPaypalButton()
    {
        $quote = $this->getFixtureQuote();
        $quote->getShippingAddress()->setShippingMethod('');

        $this->prepareCheckoutModel($quote);
        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 1);

        $this->checkoutModel->returnFromPaypal('token');

        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $exportedShippingData = $this->getExportedData()['shipping'];

        $this->assertEquals([$exportedShippingData['street']], $shippingAddress->getStreet());
        $this->assertEquals($exportedShippingData['firstname'], $shippingAddress->getFirstname());
        $this->assertEquals($exportedShippingData['city'], $shippingAddress->getCity());
        $this->assertEquals($exportedShippingData['telephone'], $shippingAddress->getTelephone());
        $this->assertEquals($exportedShippingData['email'], $shippingAddress->getEmail());

        $this->assertEquals('flatrate_flatrate', $shippingAddress->getShippingMethod());

        $this->assertEquals([$exportedShippingData['street']], $billingAddress->getStreet());
        $this->assertEquals($exportedShippingData['firstname'], $billingAddress->getFirstname());
        $this->assertEquals($exportedShippingData['city'], $billingAddress->getCity());
        $this->assertEquals($exportedShippingData['telephone'], $billingAddress->getTelephone());
        $this->assertEquals($exportedShippingData['email'], $billingAddress->getEmail());
    }

    /**
     * The case when handling address data from Paypal button.
     * System's address fields are replacing from export Paypal data.
     * Billing and Shipping address are different
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReturnFromPaypalButtonWithReturnBillingAddress()
    {
        $quote = $this->getFixtureQuote();
        $this->paypalConfig->expects($this->exactly(2))
            ->method('getValue')
            ->with('requireBillingAddress')
            ->willReturn(1);
        $this->prepareCheckoutModel($quote);
        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 1);

        $this->checkoutModel->returnFromPaypal('token');

        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $exportedBillingData = $this->getExportedData()['billing'];
        $exportedShippingData = $this->getExportedData()['shipping'];

        $this->assertEquals([$exportedShippingData['street']], $shippingAddress->getStreet());
        $this->assertEquals($exportedShippingData['firstname'], $shippingAddress->getFirstname());
        $this->assertEquals($exportedShippingData['city'], $shippingAddress->getCity());
        $this->assertEquals($exportedShippingData['telephone'], $shippingAddress->getTelephone());
        $this->assertEquals($exportedShippingData['email'], $shippingAddress->getEmail());

        $this->assertEquals([$exportedBillingData['street']], $billingAddress->getStreet());
        $this->assertEquals($exportedBillingData['firstname'], $billingAddress->getFirstname());
        $this->assertEquals($exportedBillingData['city'], $billingAddress->getCity());
        $this->assertEquals($exportedBillingData['telephone'], $billingAddress->getTelephone());
        $this->assertEquals($exportedBillingData['email'], $billingAddress->getEmail());
    }

    /**
     * The case when handling address data from the checkout.
     * System's address fields are not replacing from export PayPal data.
     * Billing and Shipping address are the same
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReturnFromPaypalIfCheckout()
    {
        $prefix = 'exported';
        $quote = $this->getFixtureQuote();
        $this->prepareCheckoutModel($quote, $prefix);
        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 0);

        $originalShippingAddress = $quote->getShippingAddress();
        $originalBillingAddress = $quote->getBillingAddress();

        $this->checkoutModel->returnFromPaypal('token');

        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();

        $this->assertEquals($originalShippingAddress->getStreet(), $shippingAddress->getStreet());
        $this->assertEquals($originalShippingAddress->getFirstname(), $shippingAddress->getFirstname());
        $this->assertEquals($originalShippingAddress->getCity(), $shippingAddress->getCity());
        $this->assertEquals($originalShippingAddress->getTelephone(), $shippingAddress->getTelephone());

        $this->assertEquals($originalBillingAddress->getStreet(), $billingAddress->getStreet());
        $this->assertEquals($originalBillingAddress->getFirstname(), $billingAddress->getFirstname());
        $this->assertEquals($originalBillingAddress->getCity(), $billingAddress->getCity());
        $this->assertEquals($originalBillingAddress->getTelephone(), $billingAddress->getTelephone());
    }

    /**
     * The case when handling address data from the checkout.
     * System's address fields are replacing billing address from export PayPal data.
     * Billing and Shipping address are different
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReturnFromPaypalIfCheckoutWithReturnBillingAddress()
    {
        $prefix = 'exported';
        $quote = $this->getFixtureQuote();
        $this->paypalConfig->expects($this->exactly(2))
            ->method('getValue')
            ->with('requireBillingAddress')
            ->willReturn(1);
        $this->prepareCheckoutModel($quote, $prefix);
        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 0);

        $originalShippingAddress = $quote->getShippingAddress();

        $this->checkoutModel->returnFromPaypal('token');

        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $exportedBillingData = $this->getExportedData()['billing'];

        $this->assertEquals($originalShippingAddress->getStreet(), $shippingAddress->getStreet());
        $this->assertEquals($originalShippingAddress->getFirstname(), $shippingAddress->getFirstname());
        $this->assertEquals($originalShippingAddress->getCity(), $shippingAddress->getCity());
        $this->assertEquals($originalShippingAddress->getTelephone(), $shippingAddress->getTelephone());

        $this->assertEquals([$prefix . $exportedBillingData['street']], $billingAddress->getStreet());
        $this->assertEquals($prefix . $exportedBillingData['firstname'], $billingAddress->getFirstname());
        $this->assertEquals($prefix . $exportedBillingData['city'], $billingAddress->getCity());
        $this->assertEquals($prefix . $exportedBillingData['telephone'], $billingAddress->getTelephone());
    }

    /**
     * Test case when customer doesn't have either billing or shipping addresses.
     * Customer add virtual product to quote and place order using PayPal Express method.
     * After return from PayPal quote billing address have to be updated by PayPal Express address.
     *
     * @magentoDataFixture Magento/Paypal/_files/virtual_quote_with_empty_billing_address.php
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testReturnFromPayPalForCustomerWithEmptyAddresses(): void
    {
        $quote = $this->getFixtureQuote();
        $this->prepareCheckoutModel($quote);
        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 0);

        $this->checkoutModel->returnFromPaypal('token');

        $billingAddress = $quote->getBillingAddress();

        $this->performQuoteAddressAssertions($billingAddress, $this->getExportedData()['billing']);
    }

    /**
     * Test case when customer doesn't have either billing or shipping addresses.
     * Customer add virtual product to quote and place order using PayPal Express method.
     * Default store country is in PayPal Express allowed specific country list.
     *
     * @magentoDataFixture Magento/Paypal/_files/virtual_quote_with_empty_billing_address.php
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store payment/paypal_express/allowspecific 1
     * @magentoConfigFixture current_store payment/paypal_express/specificcountry US,GB
     * @magentoConfigFixture current_store general/country/default US
     *
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testPaymentValidationWithAllowedSpecificCountry(): void
    {
        $quote = $this->getFixtureQuote();
        $this->prepareCheckoutModel($quote);

        $quote->getPayment()->getMethodInstance()->validate();
    }

    /**
     * Test case when customer doesn't have either billing or shipping addresses.
     * Customer add virtual product to quote and place order using PayPal Express method.
     * PayPal Express allowed specific country list doesn't contain default store country.
     *
     * @magentoDataFixture Magento/Paypal/_files/virtual_quote_with_empty_billing_address.php
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store payment/paypal_express/allowspecific 1
     * @magentoConfigFixture current_store payment/paypal_express/specificcountry US,GB
     * @magentoConfigFixture current_store general/country/default CA
     *
     * @magentoDbIsolation enabled
     ** @return void
     */
    public function testPaymentValidationWithAllowedSpecificCountryNegative(): void
    {
        $this->expectExceptionMessage("You can't use the payment type you selected to make payments to the billing country.");
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $quote = $this->getFixtureQuote();
        $this->prepareCheckoutModel($quote);
        $quote->getPayment()->getMethodInstance()->validate();
    }

    /**
     * Performs quote address assertions.
     *
     * @param Address $address
     * @param array $expected
     * @return void
     */
    private function performQuoteAddressAssertions(Address $address, array $expected): void
    {
        foreach ($expected as $key => $item) {
            $methodName = 'get' . ucfirst($key);
            if ($key === 'street') {
                $item = [$item];
            }

            $this->assertEquals($item, $address->$methodName(), 'The "'. $key . '" does not match.');
        }
    }

    /**
     * Initialize a checkout model mock.
     *
     * @param Quote $quote
     */
    private function prepareCheckoutModel(Quote $quote, $prefix = '')
    {
        $this->checkoutModel = $this->objectManager->create(
            Checkout::class,
            [
                'params'         => ['quote' => $quote, 'config' => $this->paypalConfig],
                'apiTypeFactory' => $this->apiTypeFactory,
                'paypalInfo'     => $this->paypalInfo
            ]
        );

        $exportedBillingAddress = $this->getExportedAddressFixture($this->getExportedData()['billing'], $prefix);
        $this->api->method('getExportedBillingAddress')
            ->willReturn($exportedBillingAddress);

        $exportedShippingAddress = $this->getExportedAddressFixture($this->getExportedData()['shipping'], $prefix);
        $this->api->method('getExportedShippingAddress')
            ->willReturn($exportedShippingAddress);

        $this->api->method('getShippingRateCode')
            ->willReturn('flatrate_flatrate Flat Rate - Fixed');

        $this->paypalInfo->method('importToPayment')
            ->with($this->api, $quote->getPayment());
    }

    /**
     * A Paypal response stub.
     *
     * @return array
     */
    private function getExportedData(): array
    {
        return [
            'shipping' => [
                'email'      => 'customer@example.com',
                'firstname'  => 'John',
                'lastname'   => 'Doe',
                'country'    => 'US',
                'region'     => 'Colorado',
                'region_id'  => '13',
                'city'       => 'Denver',
                'street'     => '66 Pearl St',
                'postcode'   => '80203',
                'telephone'  => '555-555-555',
            ],
            'billing' => [
                'email'      => 'customer@example.com',
                'firstname'  => 'Jane',
                'lastname'   => 'Doe',
                'country'    => 'US',
                'region'     => 'Texas',
                'region_id'  => '13',
                'city'       => 'Austin',
                'street'     => '1100 Congress Ave',
                'postcode'   => '78701',
                'telephone'  => '555-555-555'
            ]
        ];
    }

    /**
     * Verify that guest customer quota has set type of checkout.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testGuestReturnFromPaypal()
    {
        $quote = $this->getFixtureQuote();
        $paypalConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiTypeFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $paypalInfo = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->setMethods(['importToPayment'])
            ->getMock();

        $checkoutModel = $this->objectManager->create(
            Checkout::class,
            [
                'params' => ['quote' => $quote, 'config' => $paypalConfig],
                'apiTypeFactory' => $apiTypeFactory,
                'paypalInfo' => $paypalInfo
            ]
        );

        $api = $this->getMockBuilder(Nvp::class)
            ->disableOriginalConstructor()
            ->setMethods(['call', 'getExportedShippingAddress', 'getExportedBillingAddress'])
            ->getMock();

        $apiTypeFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($api));

        $exportedBillingAddress = $this->getExportedAddressFixture($quote->getBillingAddress()->getData());
        $api->expects($this->any())
            ->method('getExportedBillingAddress')
            ->will($this->returnValue($exportedBillingAddress));

        $exportedShippingAddress = $this->getExportedAddressFixture($quote->getShippingAddress()->getData());
        $api->expects($this->any())
            ->method('getExportedShippingAddress')
            ->will($this->returnValue($exportedShippingAddress));

        $this->addCountryFactory($api);
        $data = [
            'COUNTRYCODE' => $quote->getShippingAddress()->getCountryId(),
            'STATE' => 'unknown'
        ];
        $api->method('call')
            ->willReturn($data);

        $paypalInfo->expects($this->once())
            ->method('importToPayment')
            ->with($api, $quote->getPayment());

        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 1);

        $checkoutModel->returnFromPaypal('token');

        $this->assertEquals(Onepage::METHOD_GUEST, $quote->getCheckoutMethod());
    }

    /**
     * Prepare fixture for exported address.
     *
     * @param array $addressData
     * @param string $prefix
     * @return \Magento\Framework\DataObject
     */
    private function getExportedAddressFixture(array $addressData, string $prefix = ''): \Magento\Framework\DataObject
    {
        $addressDataKeys = [
            'country',
            'firstname',
            'lastname',
            'street',
            'city',
            'telephone',
            'postcode',
            'region',
            'region_id',
            'email',
        ];
        $result = [];
        foreach ($addressDataKeys as $key) {
            if (isset($addressData[$key])) {
                $result[$key] = $prefix . $addressData[$key];
            }
        }

        $fixture = new \Magento\Framework\DataObject($result);
        $fixture->setExportedKeys($addressDataKeys);
        $fixture->setData('note', 'note');

        return $fixture;
    }

    /**
     * Gets quote.
     *
     * @return Quote
     */
    private function getFixtureQuote(): Quote
    {
        /** @var Collection $quoteCollection */
        $quoteCollection = $this->objectManager->create(Collection::class);

        return $quoteCollection->getLastItem();
    }

    /**
     * Adds countryFactory to a mock.
     *
     * @param \PHPUnit\Framework\MockObject\MockObject $api
     * @throws \ReflectionException
     * @return void
     */
    private function addCountryFactory(\PHPUnit\Framework\MockObject\MockObject $api): void
    {
        $reflection = new \ReflectionClass($api);
        $property = $reflection->getProperty('_countryFactory');
        $property->setAccessible(true);
        $property->setValue($api, $this->objectManager->get(CountryFactory::class));
    }
}
