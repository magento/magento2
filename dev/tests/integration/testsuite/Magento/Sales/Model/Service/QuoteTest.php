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
use Magento\Customer\Service\V1\Dto\CustomerBuilder;
use Magento\Customer\Service\V1\Dto\AddressBuilder;
use Magento\Customer\Service\V1\Dto\Region;
use Magento\Customer\Service\V1\Dto\Customer as CustomerDto;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\CustomerServiceInterface;
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
     * @var CustomerServiceInterface
     */
    protected $_customerService;

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


    public function setUp()
    {
        $this->_addressBuilder = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\Dto\AddressBuilder'
        );
        $this->_customerBuilder = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\Dto\CustomerBuilder'
        );
        $this->_customerAccountService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAccountService'
        );
        $this->_customerService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerService'
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
        $order = $this->_serviceQuote->submitOrderWithDto();
        //Makes sure that the customer for guest checkout is not saved
        $this->assertNull($order->getCustomerId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @expectedException \Magento\Exception\InputException
     * @expectedExceptionMessage One or more input exceptions have occurred.
     */
    public function testSubmitOrderInvalidCustomerData()
    {
        $this->_prepareQuote(false);
        /** @var $order \Magento\Sales\Model\Order */
        $this->_serviceQuote->submitOrderWithDto();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testSubmitOrderExistingCustomer()
    {
        $this->_prepareQuote(false);

        $response = $this->_customerAccountService->createAccount(
            $this->getSampleCustomerEntity(),
            $this->getSampleAddressEntity(),
            'password'
        );

        $this->assertEquals('registered', $response->getStatus());

        $existingCustomerId = $response->getCustomerId();
        $customerDto = $this->_customerService->getCustomer($existingCustomerId);
        $customerDto = $this->_customerBuilder->mergeDtoWithArray(
            $customerDto,
            [CustomerDto::EMAIL => 'new@example.com']
        );
        $addresses = $this->_customerAddressService->getAddresses($existingCustomerId);
        $this->_serviceQuote->getQuote()->setCustomerData($customerDto);
        $this->_serviceQuote->getQuote()->setCustomerAddressData($addresses);
        $this->_serviceQuote->submitOrderWithDto();
        $customerId = $this->_serviceQuote->getQuote()->getCustomerData()->getCustomerId();
        $this->assertNotNull($customerId);
        //Make sure no new customer is created
        $this->assertEquals($existingCustomerId, $customerId);
        $customerDto = $this->_customerService->getCustomer($existingCustomerId);
        $this->assertEquals('new@example.com', $customerDto->getEmail());
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
        $this->_serviceQuote->submitOrderWithDto();
        $customerId = $this->_serviceQuote->getQuote()->getCustomerData()->getCustomerId();
        $this->assertNotNull($customerId);
        foreach ($this->_serviceQuote->getQuote()->getCustomerAddressData() as $address) {
            $this->assertNotNull($address->getId());
            $this->assertEquals($customerId, $address->getCustomerId());
        }
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testSubmitOrderRollbackNewCustomer()
    {
        $this->_prepareQuoteWithMockTransaction();
        $this->_serviceQuote->getQuote()->setCustomerData($this->getSampleCustomerEntity());
        $this->_serviceQuote->getQuote()->setCustomerAddressData($this->getSampleAddressEntity());
        try {
            $this->_serviceQuote->submitOrderWithDto();
        } catch (\Exception $e) {
            $this->assertEquals('submitorder exception', $e->getMessage());
        }
        $this->assertNull($this->_serviceQuote->getQuote()->getCustomerData()->getCustomerId());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testSubmitOrderRollbackExistingCustomer()
    {
        $this->_prepareQuoteWithMockTransaction();
        $response = $this->_customerAccountService->createAccount(
            $this->getSampleCustomerEntity(),
            $this->getSampleAddressEntity(),
            'password'
        );
        $this->assertEquals('registered', $response->getStatus());

        $existingCustomerId = $response->getCustomerId();
        $customerDto = $this->_customerService->getCustomer($existingCustomerId);
        $customerDto = $this->_customerBuilder->mergeDtoWithArray(
            $customerDto,
            [CustomerDto::EMAIL => 'new@example.com']
        );
        $addresses = $this->_customerAddressService->getAddresses($existingCustomerId);
        $this->_serviceQuote->getQuote()->setCustomerData($customerDto);
        $this->_serviceQuote->getQuote()->setCustomerAddressData($addresses);
        try {
            $this->_serviceQuote->submitOrderWithDto();
        } catch (\Exception $e) {
            $this->assertEquals('submitorder exception', $e->getMessage());
        }
        $this->assertEquals('email@example.com', $this->_customerService->getCustomer($existingCustomerId)->getEmail());
    }

    /**
     * Function to setup Quote for order
     *
     * @param bool $customerIsGuest
     */
    private function _prepareQuote($customerIsGuest)
    {
        $quoteFixture = $this->_prepareQuoteFixture($customerIsGuest);
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
     * @return CustomerDto
     */
    private function getSampleCustomerEntity()
    {
        $email = 'email@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $this->_customerBuilder->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        return $this->_customerBuilder->create();
    }

    /**
     * Sample Address data
     *
     * @return array
     */
    private function getSampleAddressEntity()
    {
        $this->_addressBuilder
            ->setCountryId('US')
            ->setDefaultBilling(true)
            ->setDefaultShipping(true)
            ->setPostcode('75477')
            ->setRegion(
                new Region([
                    'region_code' => 'AL',
                    'region' => 'Alabama',
                    'region_id' => 1
                ])
            )
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address1 = $this->_addressBuilder->create();

        $this->_addressBuilder
            ->setCountryId('US')
            ->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->setPostcode('47676')
            ->setRegion(
                new Region([
                    'region_code' => 'AL',
                    'region' => 'Alabama',
                    'region_id' => 1
                ])
            )
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address2 = $this->_addressBuilder->create();

        return [$address1, $address2];
    }

    /**
     * Setup $this->_serviceQuote with mock transaction object
     */
    private function _prepareQuoteWithMockTransaction()
    {
        $mockTransactionFactory = $this->getMockBuilder('\Magento\Core\Model\Resource\TransactionFactory')
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $mockTransaction = $this->getMockBuilder('\Magento\Core\Model\Resource\TransactionFactory')
            ->disableOriginalConstructor()->setMethods(['addObject', 'addCommitCallback', 'save'])->getMock();

        $mockTransactionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockTransaction));

        $mockTransaction->expects($this->any())
            ->method('addObject');
        $mockTransaction->expects($this->any())
            ->method('addCommitCallback');
        $mockTransaction->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception('submitorder exception')));

        $quoteFixture = $this->_prepareQuoteFixture(false);
        $this->_serviceQuote = Bootstrap::getObjectManager()->create(
            '\Magento\Sales\Model\Service\Quote',
            array('quote' => $quoteFixture, 'transactionFactory' => $mockTransactionFactory)
        );
    }
} 