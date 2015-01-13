<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\TestFramework\Helper\Bootstrap;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    private function convertToArray($entity)
    {
        return Bootstrap::getObjectManager()
            ->create('Magento\Framework\Api\ExtensibleDataObjectConverter')
            ->toFlatArray($entity);
    }
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testCollectTotalsWithVirtual()
    {
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');

        $product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $product->load(21);
        $quote->addProduct($product);
        $quote->collectTotals();

        $this->assertEquals(2, $quote->getItemsQty());
        $this->assertEquals(1, $quote->getVirtualItemsQty());
        $this->assertEquals(20, $quote->getGrandTotal());
        $this->assertEquals(20, $quote->getBaseGrandTotal());
    }

    public function testSetCustomerData()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        /** @var \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder */
        $customerBuilder = Bootstrap::getObjectManager()->create('Magento\Customer\Api\Data\CustomerDataBuilder');
        $expected = $this->_getCustomerDataArray();
        $customer = $customerBuilder->populateWithArray($expected)->create();


        $this->assertEquals($expected, $this->convertToArray($customer));
        $quote->setCustomer($customer);
        //
        $customer = $quote->getCustomer();
        $this->assertEquals($expected, $this->convertToArray($customer));
        $this->assertEquals('qa@example.com', $quote->getCustomerEmail());
    }

    public function testUpdateCustomerData()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $customerBuilder = Bootstrap::getObjectManager()->create('Magento\Customer\Api\Data\CustomerDataBuilder');
        $expected = $this->_getCustomerDataArray();
        //For save in repository
        $expected = $this->removeIdFromCustomerData($expected);
        $customerBuilder->populateWithArray($expected);
        $customerDataSet = $customerBuilder->create();
        $this->assertEquals($expected, $this->convertToArray($customerDataSet));
        /**
         * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
         */
        $customerRepository = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customerRepository->save($customerDataSet);
        $quote->setCustomer($customerDataSet);
        $expected = $this->_getCustomerDataArray();
        $expected = $this->changeEmailInCustomerData('test@example.com', $expected);
        $customerBuilder->populateWithArray($expected);
        $customerDataUpdated = $customerBuilder->create();
        $quote->updateCustomerData($customerDataUpdated);
        $customer = $quote->getCustomer();
        $expected = $this->changeEmailInCustomerData('test@example.com', $expected);
        ksort($expected);
        $actual = $this->convertToArray($customer);
        ksort($actual);
        $this->assertEquals($expected, $actual);
        $this->assertEquals('test@example.com', $quote->getCustomerEmail());
    }

    /**
     * Customer data is set to quote (which contains valid group ID).
     */
    public function testGetCustomerGroupFromCustomer()
    {
        /** Preconditions */
        /** @var \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder */
        $customerBuilder = Bootstrap::getObjectManager()->create('Magento\Customer\Api\Data\CustomerDataBuilder');
        $customerGroupId = 3;
        $customerData = $customerBuilder->setId(1)->setGroupId($customerGroupId)->create();
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->setCustomer($customerData);
        $quote->unsetData('customer_group_id');

        /** Execute SUT */
        $this->assertEquals($customerGroupId, $quote->getCustomerGroupId(), "Customer group ID is invalid");
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testGetCustomerTaxClassId()
    {
        /**
         * Preconditions: create quote and assign ID of customer group created in fixture to it.
         */
        $fixtureGroupCode = 'custom_group';
        $fixtureTaxClassId = 3;
        /** @var \Magento\Customer\Model\Group $group */
        $group = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Group');
        $fixtureGroupId = $group->load($fixtureGroupCode, 'customer_group_code')->getId();
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->setCustomerGroupId($fixtureGroupId);

        /** Execute SUT */
        $this->assertEquals($fixtureTaxClassId, $quote->getCustomerTaxClassId(), 'Customer tax class ID is invalid.');
    }

    /**
     * Billing and shipping address arguments are not passed, customer has default billing and shipping addresses.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testAssignCustomerWithAddressChangeAddressesNotSpecified()
    {
        /** Preconditions:
         * Customer with two addresses created
         * First address is default billing, second is default shipping.
         */
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $customerData = $this->_prepareQuoteForTestAssignCustomerWithAddressChange($quote);

        /** Execute SUT */
        $quote->assignCustomerWithAddressChange($customerData);

        /** Check if SUT caused expected effects */
        $fixtureCustomerId = 1;
        $this->assertEquals($fixtureCustomerId, $quote->getCustomerId(), 'Customer ID in quote is invalid.');
        $expectedBillingAddressData = [
            'street' => 'Green str, 67',
            'telephone' => 3468676,
            'postcode' => 75477,
            'country_id' => 'US',
            'city' => 'CityM',
            'lastname' => 'Smith',
            'firstname' => 'John',
            'customer_id' => 1,
            'customer_address_id' => 1,
            'region_id' => 1
        ];
        $billingAddress = $quote->getBillingAddress();
        foreach ($expectedBillingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $billingAddress->getData($field),
                "'{$field}' value in quote billing address is invalid."
            );
        }
        $expectedShippingAddressData = [
            'customer_address_id' => 2,
            'telephone' => 3234676,
            'postcode' => 47676,
            'country_id' => 'US',
            'city' => 'CityX',
            'street' => 'Black str, 48',
            'lastname' => 'Smith',
            'firstname' => 'John',
            'customer_id' => 1,
            'region_id' => 1
        ];
        $shippingAddress = $quote->getShippingAddress();
        foreach ($expectedShippingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $shippingAddress->getData($field),
                "'{$field}' value in quote shipping address is invalid."
            );
        }
    }

    /**
     * Billing and shipping address arguments are passed, customer has default billing and shipping addresses.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testAssignCustomerWithAddressChange()
    {
        /** Preconditions:
         * Customer with two addresses created
         * First address is default billing, second is default shipping.
         */
        /** @var \Magento\Sales\Model\Quote $quote */
        $objectManager = Bootstrap::getObjectManager();
        $quote = $objectManager->create('Magento\Sales\Model\Quote');
        $customerData = $this->_prepareQuoteForTestAssignCustomerWithAddressChange($quote);
        /** @var \Magento\Sales\Model\Quote\Address $quoteBillingAddress */
        $expectedBillingAddressData = [
            'street' => 'Billing str, 67',
            'telephone' => 16546757,
            'postcode' => 2425457,
            'country_id' => 'US',
            'city' => 'CityBilling',
            'lastname' => 'LastBilling',
            'firstname' => 'FirstBilling',
            'region_id' => 1
        ];
        $quoteBillingAddress = $objectManager->create('Magento\Sales\Model\Quote\Address');
        $quoteBillingAddress->setData($expectedBillingAddressData);

        $expectedShippingAddressData = [
            'telephone' => 787878787,
            'postcode' => 117785,
            'country_id' => 'US',
            'city' => 'CityShipping',
            'street' => 'Shipping str, 48',
            'lastname' => 'LastShipping',
            'firstname' => 'FirstShipping',
            'region_id' => 1
        ];
        $quoteShippingAddress = $objectManager->create('Magento\Sales\Model\Quote\Address');
        $quoteShippingAddress->setData($expectedShippingAddressData);

        /** Execute SUT */
        $quote->assignCustomerWithAddressChange($customerData, $quoteBillingAddress, $quoteShippingAddress);

        /** Check if SUT caused expected effects */
        $fixtureCustomerId = 1;
        $this->assertEquals($fixtureCustomerId, $quote->getCustomerId(), 'Customer ID in quote is invalid.');

        $billingAddress = $quote->getBillingAddress();
        foreach ($expectedBillingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $billingAddress->getData($field),
                "'{$field}' value in quote billing address is invalid."
            );
        }
        $shippingAddress = $quote->getShippingAddress();
        foreach ($expectedShippingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $shippingAddress->getData($field),
                "'{$field}' value in quote shipping address is invalid."
            );
        }
    }

    /**
     * Prepare quote for testing assignCustomerWithAddressChange method.
     *
     * Customer with two addresses created. First address is default billing, second is default shipping.
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function _prepareQuoteForTestAssignCustomerWithAddressChange($quote)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $objectManager->create('Magento\Customer\Model\Customer');
        $fixtureSecondAddressId = 2;
        $customer->load($fixtureCustomerId)->setDefaultShipping($fixtureSecondAddressId)->save();
        $customerData = $customerRepository->getById($fixtureCustomerId);
        $this->assertEmpty(
            $quote->getBillingAddress()->getId(),
            "Precondition failed: billing address should be empty."
        );
        $this->assertEmpty(
            $quote->getShippingAddress()->getId(),
            "Precondition failed: shipping address should be empty."
        );
        return $customerData;
    }

    /**
     * @param $email
     * @param array $customerData
     * @return array
     */
    protected function changeEmailInCustomerData($email, array $customerData)
    {
        $customerData[\Magento\Customer\Model\Data\Customer::EMAIL] = $email;
        return $customerData;
    }

    /**
     * @param array $customerData
     * @return array
     */
    protected function removeIdFromCustomerData(array $customerData)
    {
        unset($customerData[\Magento\Customer\Model\Data\Customer::ID]);
        return $customerData;
    }

    protected function _getCustomerDataArray()
    {
        return [
            \Magento\Customer\Model\Data\Customer::CONFIRMATION => 'test',
            \Magento\Customer\Model\Data\Customer::CREATED_AT => '2/3/2014',
            \Magento\Customer\Model\Data\Customer::CREATED_IN => 'Default',
            \Magento\Customer\Model\Data\Customer::DEFAULT_BILLING => 'test',
            \Magento\Customer\Model\Data\Customer::DEFAULT_SHIPPING => 'test',
            \Magento\Customer\Model\Data\Customer::DOB => '2014-02-03 00:00:00',
            \Magento\Customer\Model\Data\Customer::EMAIL => 'qa@example.com',
            \Magento\Customer\Model\Data\Customer::FIRSTNAME => 'Joe',
            \Magento\Customer\Model\Data\Customer::GENDER => 'Male',
            \Magento\Customer\Model\Data\Customer::GROUP_ID =>
                \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
            \Magento\Customer\Model\Data\Customer::ID => 1,
            \Magento\Customer\Model\Data\Customer::LASTNAME => 'Dou',
            \Magento\Customer\Model\Data\Customer::MIDDLENAME => 'Ivan',
            \Magento\Customer\Model\Data\Customer::PREFIX => 'Dr.',
            \Magento\Customer\Model\Data\Customer::STORE_ID => 1,
            \Magento\Customer\Model\Data\Customer::SUFFIX => 'Jr.',
            \Magento\Customer\Model\Data\Customer::TAXVAT => 1,
            \Magento\Customer\Model\Data\Customer::WEBSITE_ID => 1
        ];
    }
}
