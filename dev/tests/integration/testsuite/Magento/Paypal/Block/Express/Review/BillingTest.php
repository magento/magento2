<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express\Review;

use Magento\Customer\Model\Context;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class BillingTest
 */
class BillingTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Block\Express\Review\Billing */
    protected $_block;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface */
    protected $_addressRepository;

    /** @var \Magento\Quote\Model\Quote\AddressFactory */
    protected $_quoteAddressFactory;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    protected $_customerRepository;

    const FIXTURE_CUSTOMER_ID = 1;

    const FIXTURE_ADDRESS_ID = 1;

    const SAMPLE_FIRST_NAME = 'UpdatedFirstName';

    const SAMPLE_LAST_NAME = 'UpdatedLastName';

    protected function setUp()
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->_customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $this->_customerRepository->getById(self::FIXTURE_CUSTOMER_ID);

        $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
        $customerSession->setCustomerData($customer);

        $this->_addressRepository = $objectManager->get(\Magento\Customer\Api\AddressRepositoryInterface::class);
        //fetch sample address
        $address = $this->_addressRepository->getById(self::FIXTURE_ADDRESS_ID);

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteCollection */
        $quoteCollection = $objectManager->get(\Magento\Quote\Model\ResourceModel\Quote\Collection::class);
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $quoteCollection->getLastItem();
        $quote->setCustomer($customer);
        /** @var $quoteAddressFactory \Magento\Quote\Model\Quote\AddressFactory */
        $this->_quoteAddressFactory = $objectManager->get(\Magento\Quote\Model\Quote\AddressFactory::class);
        $billingAddress = $this->_quoteAddressFactory->create()->importCustomerAddressData($address);
        $quote->setBillingAddress($billingAddress);
        $quote->save();

        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = $objectManager->get(\Magento\Checkout\Model\Session::class);
        $checkoutSession->setQuoteId($quote->getId());
        $checkoutSession->setLoadInactive(true);

        $objectManager->get(\Magento\Framework\App\Http\Context::class)
            ->setValue(Context::CONTEXT_AUTH, true, false);
        $this->_block = $objectManager->get(\Magento\Framework\View\LayoutInterface::class)
            ->createBlock(
                \Magento\Paypal\Block\Express\Review\Billing::class,
                '',
                ['customerSession' => $customerSession, 'resourceSession' => $checkoutSession]
            );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_product_and_payment.php
     */
    public function testGetAddress()
    {
        $addressFromFixture = $this->_addressRepository->getById(self::FIXTURE_ADDRESS_ID);
        $address = $this->_block->getAddress();
        $this->assertEquals($addressFromFixture->getFirstname(), $address->getFirstname());
        $this->assertEquals($addressFromFixture->getLastname(), $address->getLastname());
        $this->assertEquals($addressFromFixture->getCustomerId(), $address->getCustomerId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_product_and_payment.php
     */
    public function testGetAddressNotSetInQuote()
    {
        $this->_updateQuoteCustomerName();
        $address = $this->_block->getAddress();
        //Make sure the data from sample address was set correctly to the block from customer
        $this->assertEquals(self::SAMPLE_FIRST_NAME, $address->getFirstname());
        $this->assertEquals(self::SAMPLE_LAST_NAME, $address->getLastname());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_product_and_payment.php
     */
    public function testGetFirstNameAndLastName()
    {
        $this->_updateQuoteCustomerName();
        //Make sure the data from sample address was set correctly to the block from customer
        $this->assertEquals(self::SAMPLE_FIRST_NAME, $this->_block->getFirstname());
        $this->assertEquals(self::SAMPLE_LAST_NAME, $this->_block->getLastname());
    }

    /**
     * Update Customer name in Quote
     */
    protected function _updateQuoteCustomerName()
    {
        /** @var $emptyAddress \Magento\Quote\Model\Quote\Address */
        $emptyAddress = $this->_quoteAddressFactory->create();
        $emptyAddress->setFirstname(null);
        $emptyAddress->setLastname(null);
        $this->_block->getQuote()->setBillingAddress($emptyAddress);
        $customer = $this->_customerRepository->getById(self::FIXTURE_CUSTOMER_ID);
        $customer->setFirstname(
            self::SAMPLE_FIRST_NAME
        )->setLastname(
            self::SAMPLE_LAST_NAME
        );
        $this->_block->getQuote()->setCustomer($customer);
        $this->_block->getQuote()->save();

        $this->assertEquals(self::SAMPLE_FIRST_NAME, $this->_block->getFirstname());
        $this->assertEquals(self::SAMPLE_LAST_NAME, $this->_block->getLastname());
    }
}
