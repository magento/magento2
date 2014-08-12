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
namespace Magento\Sales\Model\Service;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Customer\Service\V1\Data\CustomerDetailsBuilder;
use Magento\Customer\Service\V1\Data\AddressBuilder;
use Magento\Customer\Service\V1\Data\RegionBuilder;
use Magento\Customer\Service\V1\Data\Customer as CustomerData;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface;

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
     * @var CustomerBuilder
     */
    private $_customerBuilder;

    /**
     * @var CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * @var CustomerAddressServiceInterface
     */
    protected $_customerAddressService;

    /**
     * @var AddressBuilder
     */
    protected $_customerAddressBuilder;

    /**
     * @var CustomerDetailsBuilder
     */
    protected $_customerDetailsBuilder;

    public function setUp()
    {
        $this->_customerAddressBuilder = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\Data\AddressBuilder'
        );
        $this->_customerBuilder = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\Data\CustomerBuilder'
        );
        $this->_customerDetailsBuilder = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\Data\CustomerDetailsBuilder'
        );
        $this->_customerAccountService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAccountService'
        );
        $this->_customerAddressService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAddressService'
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

        $customerDetails = $this->_customerDetailsBuilder->setCustomer(
            $this->getSampleCustomerEntity()
        )->setAddresses(
            $this->getSampleAddressEntity()
        )->create();
        $customerData = $this->_customerAccountService->createCustomer($customerDetails, 'password');

        $existingCustomerId = $customerData->getId();
        $customerData = $this->_customerBuilder->mergeDataObjectWithArray(
            $customerData,
            array(CustomerData::EMAIL => 'new@example.com')
        );
        $addresses = $this->_customerAddressService->getAddresses($existingCustomerId);
        $this->_serviceQuote->getQuote()->setCustomerData($customerData);
        $this->_serviceQuote->getQuote()->setCustomerAddressData($addresses);
        $this->_serviceQuote->submitOrderWithDataObject();
        $customerId = $this->_serviceQuote->getQuote()->getCustomerData()->getId();
        $this->assertNotNull($customerId);
        //Make sure no new customer is created
        $this->assertEquals($existingCustomerId, $customerId);
        $customerData = $this->_customerAccountService->getCustomer($existingCustomerId);
        $this->assertEquals('new@example.com', $customerData->getEmail());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testSubmitOrderNewCustomer()
    {
        $this->_prepareQuote(false);
        $this->_serviceQuote->getQuote()->setCustomerData($this->getSampleCustomerEntity());
        $this->_serviceQuote->getQuote()->setCustomerAddressData($this->getSampleAddressEntity());
        $this->_serviceQuote->submitOrderWithDataObject();
        $customerId = $this->_serviceQuote->getQuote()->getCustomerData()->getId();
        $this->assertNotNull($customerId);
        foreach ($this->_serviceQuote->getQuote()->getCustomerAddressData() as $address) {
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
            array('quote' => $quoteFixture)
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
     * @return CustomerData
     */
    private function getSampleCustomerEntity()
    {
        $email = 'email@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $this->_customerBuilder->setStoreId(
            $storeId
        )->setEmail(
            $email
        )->setFirstname(
            $firstname
        )->setLastname(
            $lastname
        )->setGroupId(
            $groupId
        );
        return $this->_customerBuilder->create();
    }

    /**
     * Sample Address data
     *
     * @return array
     */
    private function getSampleAddressEntity()
    {
        $regionBuilder =  Bootstrap::getObjectManager()->create('\Magento\Customer\Service\V1\Data\RegionBuilder');
        $this->_customerAddressBuilder->setCountryId(
            'US'
        )->setDefaultBilling(
            true
        )->setDefaultShipping(
            true
        )->setPostcode(
            '75477'
        )->setRegion(
            $regionBuilder->setRegion('Alabama')->setRegionId(1)->setRegionCode('AL')->create()
        )->setStreet(
            array('Green str, 67')
        )->setTelephone(
            '3468676'
        )->setCity(
            'CityM'
        )->setFirstname(
            'John'
        )->setLastname(
            'Smith'
        );
        $address1 = $this->_customerAddressBuilder->create();

        $this->_customerAddressBuilder->setCountryId(
            'US'
        )->setDefaultBilling(
            false
        )->setDefaultShipping(
            false
        )->setPostcode(
            '47676'
        )->setRegion(
            $regionBuilder->setRegion('Alabama')->setRegionId(1)->setRegionCode('AL')->create()
        )->setStreet(
            array('Black str, 48')
        )->setCity(
            'CityX'
        )->setTelephone(
            '3234676'
        )->setFirstname(
            'John'
        )->setLastname(
            'Smith'
        );
        $address2 = $this->_customerAddressBuilder->create();

        return array($address1, $address2);
    }
}
