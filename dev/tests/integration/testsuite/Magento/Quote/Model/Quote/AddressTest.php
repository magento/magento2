<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
 * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Quote\Model\Quote $quote */
    protected $_quote;

    /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
    protected $_customer;

    /** @var \Magento\Quote\Model\Quote\Address */
    protected $_address;

    /**@var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
    protected $customerRepository;

    /**
     * Initialize quote and customer fixtures
     */
    public function setUp()
    {
        $this->_quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Quote\Model\Quote'
        );
        $this->_quote->load('test01', 'reserved_order_id');
        $this->_quote->setIsMultiShipping('0');

        $this->customerRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $this->_customer = $this->customerRepository->getById(1);

        /** @var \Magento\Sales\Model\Order\Address $address */
        $this->_address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Quote\Model\Quote\Address'
        );
        $this->_address->setId(1);
        $this->_address->load($this->_address->getId());
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
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\AddressRepositoryInterface');
        $customerAddressData = $addressRepository->getById($this->_customer->getDefaultBilling());
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
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\AddressRepositoryInterface');
        $this->_customer->setDefaultShipping(-1)
            ->setAddresses(
                [
                    $addressRepository->getById($this->_address->getId()),
                ]
            );

        $this->_customer = $this->customerRepository->save($this->_customer);
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
     * @magentoDbIsolation enabled
     */
    public function testSameAsBillingWhenCustomerHasDefaultShippingAddress()
    {
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\AddressRepositoryInterface');
        $this->_customer->setDefaultShipping(2)
            ->setAddresses([$addressRepository->getById($this->_address->getId())]);
        $this->_customer = $this->customerRepository->save($this->_customer);
        // we should save the customer data in order to be able to use it
        $this->_quote->setCustomer($this->_customer);

        $sameAsBilling = $this->_quote->getShippingAddress()->getSameAsBilling();
        $this->assertEquals(1, $sameAsBilling);
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
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\AddressRepositoryInterface');
        $shippingAddress->setSameAsBilling(0)
            ->setCustomerAddressData($addressRepository->getById($this->_customer->getDefaultBilling()))
            ->save();
    }

    public function unsetAddressIdDataProvider()
    {
        return [[true], [false]];
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

        /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory */
        $addressFactory = Bootstrap::getObjectManager()->create('Magento\Customer\Api\Data\AddressInterfaceFactory');
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()->create('Magento\Customer\Api\AddressRepositoryInterface');
        $addressData = $addressFactory->create()
            ->setCustomerId($customerIdFromFixture)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setTelephone('123456')
            ->setPostcode('12345')
            ->setCountryId('US')
            ->setCity($city)
            ->setStreet([$street]);
        $addressData = $addressRepository->save($addressData);
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
        $street = ['Street1'];
        $company = 'TestCompany';

        $this->_address->setStreet($street);
        $this->_address->setCompany($company);

        $customerAddress = $this->_address->exportCustomerAddress();

        $this->assertEquals($street, $customerAddress->getStreet(), 'Street was exported incorrectly.');
        $this->assertEquals($company, $customerAddress->getCompany(), 'Company was exported incorrectly.');
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

        /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory */
        $addressFactory = Bootstrap::getObjectManager()->create('Magento\Customer\Api\Data\AddressInterfaceFactory');
        $customerAddressData = $addressFactory->create()->setId($customerAddressId);
        $this->_address->setCustomerAddressData($customerAddressData);
        $this->_address->save();

        $this->assertEquals($customerId, $this->_address->getCustomerId());
        $this->assertEquals($this->_quote->getId(), $this->_address->getQuoteId());
        $this->assertEquals($customerAddressId, $this->_address->getCustomerAddressId());
    }
}
