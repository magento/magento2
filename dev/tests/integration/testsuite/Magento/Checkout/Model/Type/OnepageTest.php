<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Type;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Checkout/_files/quote_with_product_and_payment.php
 * @magentoAppArea frontend
 */
class OnepageTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Model\Type\Onepage */
    protected $_model;

    /** @var \Magento\Sales\Model\Quote */
    protected $_currentQuote;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = Bootstrap::getObjectManager()->create('Magento\Checkout\Model\Type\Onepage');
        /** @var \Magento\Sales\Model\Resource\Quote\Collection $quoteCollection */
        $quoteCollection = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Resource\Quote\Collection');
        /** @var \Magento\Sales\Model\Quote $quote */
        $this->_currentQuote = $quoteCollection->getLastItem();
        $this->_model->setQuote($this->_currentQuote);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testSaveShippingWithCustomerId()
    {
        $this->_currentQuote->setCustomerId(1)->save();
        $data = [
            'address_id' => '',
            'firstname' => 'Joe',
            'lastname' => 'Black',
            'company' => 'Lunatis',
            'street' => ['1100 Parmer', 'ln.'],
            'city' => 'Austin',
            'region_id' => '57',
            'region' => '',
            'postcode' => '78757',
            'country_id' => 'US',
            'telephone' => '(512) 999-9999',
            'fax' => '',
            'save_in_address_book' => 1,
        ];
        $this->_model->saveShipping($data, 1);

        $address = $this->_currentQuote->getShippingAddress();

        /* Verify that data from Customer Address identified by id=1 is set */
        $this->assertEquals('John', $address->getFirstname());
        $this->assertEquals('Smith', $address->getLastname());
        $this->assertEquals(['Green str, 67'], $address->getStreet());
        $this->assertEquals('CityM', $address->getCity());
        $this->assertEquals('Alabama', $address->getRegion());
        $this->assertEquals(1, $address->getRegionId());
        $this->assertEquals('75477', $address->getPostcode());
        $this->assertEquals('US', $address->getCountryId());
        $this->assertEquals('3468676', $address->getTelephone());
        $this->assertEquals('customer@example.com', $address->getEmail());
        $this->assertTrue($address->getCollectShippingRates());
        $this->assertEquals(1, $address->getCustomerAddressId());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testSaveShippingWithData()
    {
        $data = [
            'address_id' => '',
            'firstname' => 'Joe',
            'lastname' => 'Black',
            'company' => 'Lunatis',
            'street' => ['1100 Parmer', 'ln.'],
            'city' => 'Austin',
            'region_id' => '57',
            'region' => '',
            'postcode' => '78757',
            'country_id' => 'US',
            'telephone' => '(512) 999-9999',
            'fax' => '',
            'save_in_address_book' => 1,
        ];
        $this->_model->saveShipping($data, null);

        $address = $this->_currentQuote->getShippingAddress();

        /* Verify that data from the form is set */
        $this->assertEquals('Joe', $address->getFirstname());
        $this->assertEquals('Black', $address->getLastname());
        $this->assertEquals('Lunatis', $address->getCompany());
        $this->assertEquals("1100 Parmer\nln.", $address->getData('street'));
        $this->assertEquals('Austin', $address->getCity());
        $this->assertEquals('US', $address->getCountryId());
        $this->assertEquals('Texas', $address->getRegion());
        $this->assertEquals('57', $address->getRegionId());
        $this->assertEquals('78757', $address->getPostcode());
        $this->assertEquals('(512) 999-9999', $address->getTelephone());
        $this->assertNull($address->getCustomerAddressId());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testSaveOrder()
    {
        $this->markTestIncomplete('MAGETWO-31257');
        $this->_model->saveBilling($this->_getCustomerData(), null);
        $this->_prepareQuote($this->_getQuote());

        $this->_model->saveOrder();

        /** @var $order \Magento\Sales\Model\Order */
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId($this->_model->getLastOrderId());

        $this->assertNotEmpty(
            $this->_model->getQuote()->getShippingAddress()->getCustomerAddressId(),
            'Quote shipping CustomerAddressId should not be empty'
        );
        $this->assertNotEmpty(
            $this->_model->getQuote()->getBillingAddress()->getCustomerAddressId(),
            'Quote billing CustomerAddressId should not be empty'
        );

        $this->assertNotEmpty(
            $order->getShippingAddress()->getCustomerAddressId(),
            'Order shipping CustomerAddressId should not be empty'
        );
        $this->assertNotEmpty(
            $order->getBillingAddress()->getCustomerAddressId(),
            'Order billing CustomerAddressId should not be empty'
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testInitCheckoutNotLoggedIn()
    {
        /* The customer session must be cleared before the real test begins. Need to
           have a customer via the data fixture to actually log out. */
        /** @var $customerSession \Magento\Customer\Model\Session*/
        $customerSession = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session');
        $customerSession->setCustomerId(1);
        $customerSession->logout();

        $this->_model->saveBilling($this->_getCustomerData(), null);
        $this->_prepareQuote($this->_getQuote());
        $this->assertTrue($this->_model->getCheckout()->getSteps()['shipping']['allow']);
        $this->assertTrue($this->_model->getCheckout()->getSteps()['billing']['allow']);
        $this->_model->initCheckout();
        $this->assertFalse($this->_model->getCheckout()->getSteps()['shipping']['allow']);
        $this->assertFalse($this->_model->getCheckout()->getSteps()['billing']['allow']);
        $this->assertNull($this->_model->getQuote()->getCustomer()->getEmail());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testInitCheckoutLoggedIn()
    {
        $this->_model->saveBilling($this->_getCustomerData(), null);
        $this->_prepareQuote($this->_getQuote());
        $customerIdFromFixture = 1;
        $emailFromFixture = 'customer@example.com';
        /** @var $customerSession \Magento\Customer\Model\Session*/
        $customerSession = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session');
        /** @var $customerRepository \Magento\Customer\Api\CustomerRepositoryInterface */
        $customerRepository = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $customerData = $customerRepository->getById($customerIdFromFixture);
        $customerSession->setCustomerDataObject($customerData);
        $this->_model = Bootstrap::getObjectManager()->create(
            'Magento\Checkout\Model\Type\Onepage',
            ['customerSession' => $customerSession]
        );
        $this->assertTrue($this->_model->getCheckout()->getSteps()['shipping']['allow']);
        $this->assertTrue($this->_model->getCheckout()->getSteps()['billing']['allow']);
        $this->_model->initCheckout();
        $this->assertFalse($this->_model->getCheckout()->getSteps()['shipping']['allow']);
        //When the user is logged in and for Step billing - allow is not reset to true
        $this->assertTrue($this->_model->getCheckout()->getSteps()['billing']['allow']);
        $this->assertEquals($emailFromFixture, $this->_model->getQuote()->getCustomer()->getEmail());
    }

    /**
     * New customer, the same address should be used for shipping and billing, it should be persisted to DB.
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSaveBillingSameAsShipping()
    {
        $quote = $this->_model->getQuote();

        /** Preconditions */
        $customerData = $this->_getCustomerData();
        $customerAddressId = false;
        $this->assertEquals(1, $customerData['use_for_shipping'], "Precondition failed: use_for_shipping is invalid");
        $this->assertEquals(
            1,
            $customerData['save_in_address_book'],
            "Precondition failed: save_in_address_book is invalid"
        );
        $this->assertEmpty(
            $quote->getBillingAddress()->getId(),
            "Precondition failed: billing address must not be initialized."
        );
        $this->assertEmpty(
            $quote->getShippingAddress()->getId(),
            "Precondition failed: billing address must not be initialized."
        );

        /** Execute SUT */
        $result = $this->_model->saveBilling($customerData, $customerAddressId);
        $this->assertEquals([], $result, 'Return value is invalid');

        /** Ensure that quote addresses were persisted correctly */
        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        $quoteAddressFieldsToCheck = [
            'quote_id' => $quote->getId(),
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'John.Smith@example.com',
            'street' => '6131 Monterey Rd, Apt 1',
            'city' => 'Los Angeles',
            'postcode' => '90042',
            'country_id' => 'US',
            'region_id' => '1',
            'region' => 'Alabama',
            'telephone' => '(323) 255-5861',
            'customer_id' => null,
            'customer_address_id' => null,
        ];

        foreach ($quoteAddressFieldsToCheck as $field => $value) {
            $this->assertEquals($value, $billingAddress->getData($field), "{$field} value is invalid");
            $this->assertEquals($value, $shippingAddress->getData($field), "{$field} value is invalid");
        }
        $this->assertEquals('1', $shippingAddress->getData('same_as_billing'), "same_as_billing value is invalid");
        $this->assertGreaterThan(0, $shippingAddress->getData('address_id'), "address_id value is invalid");
        $this->assertGreaterThan(0, $billingAddress->getData('address_id'), "address_id value is invalid");
        $this->assertEquals(
            1,
            $billingAddress->getData('save_in_address_book'),
            "save_in_address_book value is invalid"
        );
        $this->assertEquals(
            0,
            $shippingAddress->getData('save_in_address_book'),
            "As soon as 'same_as_billing' is set to 1, 'save_in_address_book' of shipping should be 0"
        );

        /** Ensure that customer-related data was ported to quote correctly */
        $quoteFieldsToCheck = [
            'customer_firstname' => 'John',
            'customer_lastname' => 'Smith',
            'customer_email' => 'John.Smith@example.com',
        ];
        foreach ($quoteFieldsToCheck as $field => $value) {
            $this->assertEquals($value, $quote->getData($field), "{$field} value is set to quote incorrectly.");
        }

        /** Perform if checkout steps status was correctly updated in session */
        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = Bootstrap::getObjectManager()->get('Magento\Checkout\Model\Session');
        $this->assertTrue($checkoutSession->getStepData('billing', 'allow'), 'Billing step should be allowed.');
        $this->assertTrue($checkoutSession->getStepData('billing', 'complete'), 'Billing step should be completed.');
        $this->assertTrue($checkoutSession->getStepData('shipping', 'allow'), 'Shipping step should be allowed.');
    }

    /**
     * New customer, billing address should not be used as shipping address, it should be persisted to DB.
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSaveBilling()
    {
        $quote = $this->_model->getQuote();

        /** Preconditions */
        $customerData = $this->_getCustomerData();
        $customerData['use_for_shipping'] = 0;
        $customerAddressId = false;
        $this->assertEquals(
            1,
            $customerData['save_in_address_book'],
            "Precondition failed: save_in_address_book is invalid"
        );
        $this->assertEmpty(
            $quote->getBillingAddress()->getId(),
            "Precondition failed: billing address must not be initialized."
        );
        $this->assertEmpty(
            $quote->getShippingAddress()->getId(),
            "Precondition failed: billing address must not be initialized."
        );

        /** Execute SUT */
        $result = $this->_model->saveBilling($customerData, $customerAddressId);
        $this->assertEquals([], $result, 'Return value is invalid');

        /** Ensure that quote addresses were persisted correctly */
        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        $quoteAddressFieldsToCheck = [
            'quote_id' => $quote->getId(),
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'John.Smith@example.com',
            'street' => '6131 Monterey Rd, Apt 1',
            'city' => 'Los Angeles',
            'postcode' => '90042',
            'country_id' => 'US',
            'region_id' => '1',
            'region' => 'Alabama',
            'telephone' => '(323) 255-5861',
            'customer_id' => null,
            'customer_address_id' => null,
        ];

        foreach ($quoteAddressFieldsToCheck as $field => $value) {
            $this->assertEquals($value, $billingAddress->getData($field), "{$field} value is invalid");
        }
        $this->assertGreaterThan(0, $billingAddress->getData('address_id'), "address_id value is invalid");
        $this->assertEmpty(
            $shippingAddress->getData('firstname'),
            "Shipping address should not be populated with billing address data when 'same_as_billing' is set to 0."
        );
        $this->assertEquals(
            1,
            $billingAddress->getData('save_in_address_book'),
            "save_in_address_book value is invalid"
        );

        /** Ensure that customer-related data was ported to quote correctly */
        $quoteFieldsToCheck = [
            'customer_firstname' => 'John',
            'customer_lastname' => 'Smith',
            'customer_email' => 'John.Smith@example.com',
        ];
        foreach ($quoteFieldsToCheck as $field => $value) {
            $this->assertEquals($value, $quote->getData($field), "{$field} value is set to quote incorrectly.");
        }

        /** Perform if checkout steps status was correctly updated in session */
        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = Bootstrap::getObjectManager()->get('Magento\Checkout\Model\Session');
        $this->assertTrue($checkoutSession->getStepData('billing', 'allow'), 'Billing step should be allowed.');
        $this->assertTrue($checkoutSession->getStepData('billing', 'complete'), 'Billing step should be completed.');
        $this->assertTrue($checkoutSession->getStepData('shipping', 'allow'), 'Shipping step should be allowed.');
    }

    /**
     * New address, address data is invalid.
     */
    public function testSaveBillingValidationErrorNewAddress()
    {
        /** Preconditions */
        $customerData = $this->_getCustomerData();
        unset($customerData['firstname']);
        $customerAddressId = false;

        /** Execute SUT */
        $result = $this->_model->saveBilling($customerData, $customerAddressId);
        $validationErrors = [
            '"First Name" is a required value.',
            '"First Name" length must be equal or greater than 1 characters.',
        ];
        $this->assertEquals(
            ['error' => 1, 'message' => $validationErrors],
            $result,
            'Validation error is invalid.'
        );
    }

    /**
     * Existing address, address data is invalid.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testSaveBillingExistingAddressInvalidData()
    {
        /** Preconditions */
        $addressIdFromFixture = 1;
        $customerIdFromFixture = 1;
        $customerData = $this->_getCustomerData();
        unset($customerData['firstname']);
        $this->_getQuote()->setCustomerId($customerIdFromFixture);

        /** Execute SUT */
        /**
         * If customer address is available, provided customer data is not validated,
         * that's why no error occurs when invalid data is provided
         */
        $result = $this->_model->saveBilling($customerData, $addressIdFromFixture);
        $this->assertEquals([], $result, 'No errors expected.');
    }

    /**
     * Address exists, but it does not belong to the current customer which is set to quote.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testSaveBillingInvalidAddressId()
    {
        /** Preconditions */
        $addressIdFromFixture = 1;
        $customerData = $this->_getCustomerData();
        unset($customerData['firstname']);
        /** Any ID can be used, which is not equal to ID of customer to which current address belongs. */
        $secondCustomerId = 2;
        $this->_getQuote()->setCustomerId($secondCustomerId);

        /** Execute SUT */
        $result = $this->_model->saveBilling($customerData, $addressIdFromFixture);
        $validationErrors = 'The customer address is not valid.';
        $this->assertEquals(
            ['error' => 1, 'message' => $validationErrors],
            $result,
            'Validation error is invalid.'
        );
    }

    /**
     * Empty data.
     */
    public function testSaveBillingEmptyData()
    {
        /** Execute SUT */
        $customerData = [];
        $customerAddressId = false;
        $result = $this->_model->saveBilling($customerData, $customerAddressId);
        $this->assertEquals(
            ['error' => -1, 'message' => 'Invalid data'],
            $result,
            'Validation error is invalid.'
        );
    }

    /**
     * Address does not exist, but existing email is specified in address data.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSaveBillingNewAddressErrorExistingEmail()
    {
        /** Preconditions */
        $customerData = $this->_getCustomerData();
        $fixtureCustomerEmail = 'customer@example.com';
        $customerData['email'] = $fixtureCustomerEmail;
        $customerAddressId = false;
        $this->_getQuote()->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);

        /** Execute SUT */
        $result = $this->_model->saveBilling($customerData, $customerAddressId);
        $this->assertArrayHasKey('message', $result, 'Error message was expected to be set');
        $this->assertStringStartsWith(
            'There is already a registered customer using this email address',
            $result['message'],
            'Validation error is invalid.'
        );
    }

    /**
     * New address, customer address is invalid (customer validation should fail, not address validation).
     */
    public function testSaveBillingInvalidCustomerData()
    {
        /** Preconditions */
        $customerData = $this->_getCustomerData();
        $customerData['email'] = 'invalidemail';
        $this->_getQuote()->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
        $customerAddressId = false;

        /** Execute SUT */
        $result = $this->_model->saveBilling($customerData, $customerAddressId);
        $validationErrors = '"Email" is not a valid email address.';
        $this->assertEquals(
            ['error' => -1, 'message' => $validationErrors],
            $result,
            'Validation error is invalid.'
        );
    }

    /**
     * @return \Magento\Sales\Model\Quote
     */
    protected function _getQuote()
    {
        return $this->_currentQuote;
    }

    /**
     * Prepare Quote
     *
     * @param \Magento\Sales\Model\Quote $quote
     */
    protected function _prepareQuote($quote)
    {
        /** @var $rate \Magento\Sales\Model\Quote\Address\Rate */
        $rate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Quote\Address\Rate'
        );
        $rate->setCode('freeshipping_freeshipping');
        $rate->getPrice(1);

        $quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');
        $quote->getShippingAddress()->addShippingRate($rate);
        $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
    }

    /**
     * Customer data for quote
     *
     * @return array
     */
    protected function _getCustomerData()
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'John.Smith@example.com',
            'street' => ['6131 Monterey Rd, Apt 1', ''],
            'city' => 'Los Angeles',
            'postcode' => '90042',
            'country_id' => 'US',
            'region_id' => '1',
            'telephone' => '(323) 255-5861',
            'customer_password' => 'password',
            'confirm_password' => 'password',
            'save_in_address_book' => '1',
            'use_for_shipping' => '1'
        ];
    }
}
