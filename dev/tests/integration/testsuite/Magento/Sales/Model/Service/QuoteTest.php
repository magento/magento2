<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Service\Quote
     */
    protected $_serviceQuote;

    /**
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    private $customerBuilder;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressDataBuilder
     */
    protected $addressBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->addressBuilder = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\Data\AddressDataBuilder'
        );
        $this->customerBuilder = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\Data\CustomerDataBuilder'
        );
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $this->addressRepository = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AddressRepositoryInterface'
        );
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSubmitGuestOrder()
    {
        $this->_prepareQuote(true);
        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->_serviceQuote->submitOrderWithDataObject();
        //Makes sure that the customer for guest checkout is not saved
        $this->assertNull($order->getCustomerId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage One or more input exceptions have occurred.
     */
    public function testSubmitOrderInvalidCustomerData()
    {
        $this->_prepareQuote(false);
        /** @var $order \Magento\Sales\Model\Order */
        $this->_serviceQuote->submitOrderWithDataObject();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testSubmitOrderExistingCustomer()
    {
        $this->_prepareQuote(false);
        $customer = $this->getSampleCustomerEntity();
        $existingCustomerId = $customer->getId();

        $addresses = $this->getSampleAddressEntity($existingCustomerId);

        $customer = $this->customerBuilder->mergeDataObjectWithArray(
            $customer,
            [CustomerInterface::EMAIL => 'new@example.com']
        )->setAddresses($addresses)
            ->create();
        $customer = $this->customerRepository->save($customer);
        $addresses = $customer->getAddresses();
        $this->_serviceQuote->getQuote()->setCustomer($customer);
        $this->_serviceQuote->getQuote()->setCustomerAddressData($addresses);
        $this->_serviceQuote->submitOrderWithDataObject();
        $customerId = $this->_serviceQuote->getQuote()->getCustomer()->getId();
        $this->assertNotNull($customerId);
        //Make sure no new customer is created
        $this->assertEquals($existingCustomerId, $customerId);
        $customerData = $this->customerRepository->getById($existingCustomerId);
        $this->assertEquals('new@example.com', $customerData->getEmail());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testSubmitOrderNewCustomer()
    {
        $this->_prepareQuote(false);
        $customer = $this->getSampleCustomerEntity();
        $this->_serviceQuote->getQuote()->setCustomer($customer);
        $this->_serviceQuote->getQuote()->setCustomerAddressData($this->getSampleAddressEntity($customer->getId()));
        $this->_serviceQuote->submitOrderWithDataObject();
        $customerId = $this->_serviceQuote->getQuote()->getCustomer()->getId();
        $this->assertNotNull($customerId);
        foreach ($this->_serviceQuote->getQuote()->getCustomer()->getAddresses() as $address) {
            $this->assertNotNull($address->getId());
            $this->assertEquals($customerId, $address->getCustomerId());
        }
    }

    /**
     * Function to setup Quote for order
     *
     * @param bool $customerIsGuest
     */
    private function _prepareQuote($customerIsGuest)
    {
        $quoteFixture = $this->_prepareQuoteFixture($customerIsGuest);
        $quoteFixture->setCustomerEmail('admin@example.com');
        $this->_serviceQuote = Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Service\Quote',
            ['quote' => $quoteFixture]
        );
    }

    /**
     * Prepare quote data
     *
     * @param bool $customerIsGuest
     * @return \Magento\Sales\Model\Quote
     */
    private function _prepareQuoteFixture($customerIsGuest)
    {
        $method = 'freeshipping_freeshipping';
        /** @var $quoteFixture \Magento\Sales\Model\Quote */
        $quoteFixture = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quoteFixture->load('test01', 'reserved_order_id');
        $rate = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote\Address\Rate');
        $rate->setCode($method);
        $quoteFixture->getShippingAddress()->addShippingRate($rate);
        $quoteFixture->getShippingAddress()->setShippingMethod($method);
        $quoteFixture->setCustomerIsGuest($customerIsGuest);
        return $quoteFixture;
    }

    /**
     * Sample customer data
     *
     * @return CustomerInterface
     */
    private function getSampleCustomerEntity()
    {
        $email = 'email@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;
        $password = 'password';

        $customer = $this->customerBuilder->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId)
            ->create();

        return $this->accountManagement->createAccount($customer, $password);
    }

    /**
     * Sample Address data
     *
     * @return array
     */
    private function getSampleAddressEntity($customerId)
    {
        /** @var \Magento\Customer\Api\Data\RegionDataBuilder $regionBuilder */
        $regionBuilder =  Bootstrap::getObjectManager()->get('Magento\Customer\Api\Data\RegionDataBuilder');
        $address1 = $this->addressBuilder->setCountryId('US')
            ->setCustomerId($customerId)
            ->setDefaultBilling(true)
            ->setDefaultShipping(true)
            ->setPostcode(75477)
            ->setRegion($regionBuilder->setRegion('Alabama')->setRegionId(1)->setRegionCode('AL')->create())
            ->setStreet(['Green str, 67'])
            ->setTelephone(3468676)
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->create();
        $address1 = $this->addressRepository->save($address1);

        $address2 = $this->addressBuilder->setCountryId('US')
            ->setCustomerId($customerId)
            ->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->setPostcode(47676)
            ->setRegion($regionBuilder->setRegion('Alabama')->setRegionId(1)->setRegionCode('AL')->create())
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone(3234676)
            ->setFirstname('John')
            ->setLastname('Smith')
            ->create();
        $address2 = $this->addressRepository->save($address2);

        return [$address1, $address2];
    }
}
