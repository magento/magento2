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
namespace Magento\Sales\Model\Quote;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
 * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Quote $quote */
    protected $_quote;

    /** @var \Magento\Customer\Model\Customer $customer */
    protected $_customer;

    /** @var \Magento\Sales\Model\Quote\Address */
    protected $_address;

    /**
     * Initialize quote and customer fixtures
     */
    public function setUp()
    {
        $this->_quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Quote'
        );
        $this->_quote->load('test01', 'reserved_order_id');
        $this->_quote->setIsMultiShipping('0');

        /** @var \Magento\Customer\Model\Customer $customer */
        $this->_customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Customer'
        );
        $this->_customer->load(1);

        /** @var \Magento\Sales\Model\Order\Address $address */
        $this->_address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Quote\Address'
        );
        $this->_address->load(1);
        $this->_address->setQuote($this->_quote);
    }

    protected function tearDown()
    {
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\CustomerRegistry');
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * same_as_billing must be equal 0 if billing address is being saved
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     */
    public function testSameAsBillingForBillingAddress($unsetId)
    {
        $this->_quote->setCustomer($this->_customer);
        $address = $this->_quote->getBillingAddress();
        if ($unsetId) {
            $address->setId(null);
        }
        /** @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService */
        $addressService = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Service\V1\CustomerAddressServiceInterface');
        $customerAddressData = $addressService->getDefaultBillingAddress($this->_customer->getId());
        $address->setSameAsBilling(0)->setCustomerAddressData($customerAddressData)->save();
        $this->assertEquals(0, $this->_quote->getBillingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer is guest
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     */
    public function testSameAsBillingWhenCustomerIsGuest($unsetId)
    {
        $shippingAddress = $this->_quote->getShippingAddress();
        if ($unsetId) {
            $shippingAddress->setId(null);
        }
        $shippingAddress->setSameAsBilling(0);
        $shippingAddress->save();
        $this->assertEquals((int)$unsetId, $shippingAddress->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if quote address has no customer address
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     */
    public function testSameAsBillingWhenQuoteAddressHasNoCustomerAddress($unsetId)
    {
        $this->_quote->setCustomer($this->_customer);
        $shippingAddress = $this->_quote->getShippingAddress();
        if ($unsetId) {
            $shippingAddress->setId(null);
        }
        $shippingAddress->setSameAsBilling(0)
            ->setCustomerAddressData(null)
            ->save();
        $this->assertEquals((int)$unsetId, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer registered and he has no default shipping address
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSameAsBillingWhenCustomerHasNoDefaultShippingAddress($unsetId)
    {
        $this->_customer->setDefaultShipping(-1)->save();
        // we should save the customer data in order to be able to use it
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave($unsetId);
        $sameAsBilling = $this->_quote->getShippingAddress()->getSameAsBilling();
        $this->assertEquals((int)$unsetId, $sameAsBilling);
    }

    /**
     * same_as_billing must be equal 1 if customer has the same billing and shipping address
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSameAsBillingWhenCustomerHasBillingSameShipping($unsetId)
    {
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave($unsetId);
        $this->assertEquals((int)$unsetId, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 0 if customer has default shipping address that differs from default billing
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSameAsBillingWhenCustomerHasDefaultShippingAddress($unsetId)
    {
        $this->_customer->setDefaultShipping(2)->save();
        // we should save the customer data in order to be able to use it
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave($unsetId);
        $sameAsBilling = $this->_quote->getShippingAddress()->getSameAsBilling();
        $this->assertEquals(0, $sameAsBilling);
    }

    /**
     * Assign customer address to quote address and save quote address
     *
     * @param bool $unsetId
     */
    protected function _setCustomerAddressAndSave($unsetId)
    {
        $shippingAddress = $this->_quote->getShippingAddress();
        if ($unsetId) {
            $shippingAddress->setId(null);
        }
        $shippingAddress->setSameAsBilling(0)
            ->setCustomerAddressData($this->_customer->getDefaultBillingAddress())
            ->save();
    }

    public function unsetAddressIdDataProvider()
    {
        return array(array(true), array(false));
    }

    /**
     * Import customer address to quote address
     */
    public function testImportCustomerAddressDataWithCustomer()
    {
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $city = 'TestCity';
        $street = 'Street1';

        /** @var \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder */
        $addressBuilder = Bootstrap::getObjectManager()->create('Magento\Customer\Service\V1\Data\AddressBuilder');
        $addressData = $addressBuilder->setCustomerId(
            $customerIdFromFixture
        )->setCity(
            $city
        )->setStreet(
            [$street]
        )->create();
        $this->_address->setQuote($this->_quote);
        $this->_address->importCustomerAddressData($addressData);

        $this->assertEquals($customerEmailFromFixture, $this->_address->getEmail(), 'Email was imported incorrectly.');
        $this->assertEquals($city, $this->_address->getCity(), 'City was imported incorrectly.');
        $this->assertEquals($street, $this->_address->getStreetFull(), 'Imported street is invalid.');
    }

    /**
     * Export customer address from quote address
     */
    public function testExportCustomerAddressData()
    {
        $street = array('Street1');
        $company = 'TestCompany';

        $this->_address->setStreet($street);
        $this->_address->setCompany($company);

        $customerAddress = $this->_address->exportCustomerAddressData();

        $this->assertEquals($street, $customerAddress->getStreet(), 'Street was exported incorrectly.');
        $this->assertEquals($company, $customerAddress->getCompany(), 'Company was exported incorrectly.');
    }

    /**
     * Import order address to quote address
     */
    public function testImportOrderAddress()
    {
        $street = 'Street1';
        $email = 'test_email@example.com';

        /** @var \Magento\Sales\Model\Order\Address $orderAddress */
        $orderAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Order\Address'
        );

        $orderAddress->setStreet($street);
        $orderAddress->setEmail($email);
        $this->_address->importOrderAddress($orderAddress);

        $this->assertEquals($street, $this->_address->getStreet1(), 'Expected street does not exists');
        $this->assertEquals($email, $orderAddress->getEmail(), 'Expected email does not exists');
    }

    public function testPopulateBeforeSaveData()
    {
        /** Preconditions */
        $customerId = 1;
        $customerAddressId = 1;

        $this->_address->setQuote($this->_quote);
        $this->assertNotEquals(
            $customerId,
            $this->_address->getCustomerId(),
            "Precondition failed: Customer ID was not set."
        );
        $this->assertNotEquals(1, $this->_address->getQuoteId(), "Precondition failed: Quote ID was not set.");
        $this->assertNotEquals(
            $customerAddressId,
            $this->_address->getCustomerAddressId(),
            "Precondition failed: Customer address ID was not set."
        );

        /** @var \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder */
        $addressBuilder = Bootstrap::getObjectManager()->create('Magento\Customer\Service\V1\Data\AddressBuilder');
        $customerAddressData = $addressBuilder->setId($customerAddressId)->create();
        $this->_address->setCustomerAddressData($customerAddressData);
        $this->_address->save();

        $this->assertEquals($customerId, $this->_address->getCustomerId());
        $this->assertEquals($this->_quote->getId(), $this->_address->getQuoteId());
        $this->assertEquals($customerAddressId, $this->_address->getCustomerAddressId());
    }
}
