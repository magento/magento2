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
namespace Magento\Sales\Model;

use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Customer\Service\V1\Data\Customer;
use Magento\TestFramework\Helper\Bootstrap;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
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
        $customerMetadataService = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerMetadataService'
        );
        $customerBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            [
                'metadataService' => $customerMetadataService
            ]
        );
        $expected = $this->_getCustomerDataArray();
        $customerBuilder->populateWithArray($expected);

        $customerDataSet = $customerBuilder->create();
        $this->assertEquals($expected, $customerDataSet->__toArray());
        $quote->setCustomerData($customerDataSet);

        $customerDataRetrieved = $quote->getCustomerData();
        $this->assertEquals($expected, $customerDataRetrieved->__toArray());
        $this->assertEquals('qa@example.com', $quote->getCustomerEmail());
    }

    public function testUpdateCustomerData()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $customerMetadataService = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerMetadataService'
        );
        $customerBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            [
                'metadataService' => $customerMetadataService
            ]
        );
        $expected = $this->_getCustomerDataArray();

        $customerBuilder->populateWithArray($expected);
        $customerDataSet = $customerBuilder->create();
        $this->assertEquals($expected, $customerDataSet->__toArray());
        $quote->setCustomerData($customerDataSet);

        $expected[Customer::EMAIL] = 'test@example.com';
        $customerBuilder->populateWithArray($expected);
        $customerDataUpdated = $customerBuilder->create();

        $quote->updateCustomerData($customerDataUpdated);
        $customerDataRetrieved = $quote->getCustomerData();
        $this->assertEquals($expected, $customerDataRetrieved->__toArray());
        $this->assertEquals('test@example.com', $quote->getCustomerEmail());
    }

    /**
     * Customer data is set to quote (which contains valid group ID).
     */
    public function testGetCustomerGroupFromCustomer()
    {
        /** Preconditions */
        /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder */
        $customerBuilder = Bootstrap::getObjectManager()->create('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $customerGroupId = 3;
        $customerData = $customerBuilder->setId(1)->setGroupId($customerGroupId)->create();
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->setCustomerData($customerData);
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
        $expectedBillingAddressData = array(
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
        );
        $billingAddress = $quote->getBillingAddress();
        foreach ($expectedBillingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $billingAddress->getData($field),
                "'{$field}' value in quote billing address is invalid."
            );
        }
        $expectedShippingAddressData = array(
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
        );
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
        $expectedBillingAddressData = array(
            'street' => 'Billing str, 67',
            'telephone' => 16546757,
            'postcode' => 2425457,
            'country_id' => 'US',
            'city' => 'CityBilling',
            'lastname' => 'LastBilling',
            'firstname' => 'FirstBilling',
            'region_id' => 1
        );
        $quoteBillingAddress = $objectManager->create('Magento\Sales\Model\Quote\Address');
        $quoteBillingAddress->setData($expectedBillingAddressData);

        $expectedShippingAddressData = array(
            'telephone' => 787878787,
            'postcode' => 117785,
            'country_id' => 'US',
            'city' => 'CityShipping',
            'street' => 'Shipping str, 48',
            'lastname' => 'LastShipping',
            'firstname' => 'FirstShipping',
            'region_id' => 1
        );
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
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    protected function _prepareQuoteForTestAssignCustomerWithAddressChange($quote)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService */
        $customerService = $objectManager->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $objectManager->create('Magento\Customer\Model\Customer');
        $fixtureSecondAddressId = 2;
        $customer->load($fixtureCustomerId)->setDefaultShipping($fixtureSecondAddressId)->save();
        $customerData = $customerService->getCustomer($fixtureCustomerId);
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

    protected function _getCustomerDataArray()
    {
        return array(
            Customer::ID => 1,
            Customer::CONFIRMATION => 'test',
            Customer::CREATED_AT => '2/3/2014',
            Customer::CREATED_IN => 'Default',
            Customer::DEFAULT_BILLING => 'test',
            Customer::DEFAULT_SHIPPING => 'test',
            Customer::DOB => '2/3/2014',
            Customer::EMAIL => 'qa@example.com',
            Customer::FIRSTNAME => 'Joe',
            Customer::GENDER => 'Male',
            Customer::GROUP_ID => \Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID,
            Customer::LASTNAME => 'Dou',
            Customer::MIDDLENAME => 'Ivan',
            Customer::PREFIX => 'Dr.',
            Customer::STORE_ID => 1,
            Customer::SUFFIX => 'Jr.',
            Customer::TAXVAT => 1,
            Customer::WEBSITE_ID => 1
        );
    }
}
