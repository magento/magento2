<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\TestFramework\Helper\Bootstrap;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    protected function setUp()
    {
        $this->_checkoutSession = Bootstrap::getObjectManager()->create('Magento\Checkout\Model\Session');
        parent::setUp();
    }

    /**
     * Test covers case when quote is not yet initialized and customer data is set to checkout session model.
     *
     * Expected result - quote object should be loaded and customer data should be set to it.
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     */
    public function testGetQuoteNotInitializedCustomerSet()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $customerRepository->getById(1);
        $this->_checkoutSession->setCustomerData($customer);

        /** Execute SUT */
        $quote = $this->_checkoutSession->getQuote();
        $this->_validateCustomerDataInQuote($quote);
    }

    /**
     * Test covers case when quote is not yet initialized and customer data is set to customer session model.
     *
     * Expected result - quote object should be loaded and customer data should be set to it.
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetQuoteNotInitializedCustomerLoggedIn()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $customerRepository->getById(1);
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $customerSession->setCustomerDataObject($customer);

        /** Execute SUT */
        $quote = $this->_checkoutSession->getQuote();
        $this->_validateCustomerDataInQuote($quote);
    }

    /**
     * Tes merging of customer data into initialized quote object.
     *
     * Conditions:
     * 1. Quote without customer data is set to checkout session
     * 2. Customer without associated quote is set to checkout session
     *
     * Expected result:
     * Quote which is set to checkout session should contain customer data
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testLoadCustomerQuoteCustomerWithoutQuote()
    {
        $quote = $this->_checkoutSession->getQuote();
        $this->assertEmpty($quote->getCustomerId(), 'Precondition failed: Customer data must not be set to quote');
        $this->assertEmpty($quote->getCustomerEmail(), 'Precondition failed: Customer data must not be set to quote');

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $customerRepository->getById(1);
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $customerSession->setCustomerDataObject($customer);

        /** Ensure that customer data is still unavailable before SUT invocation */
        $quote = $this->_checkoutSession->getQuote();
        $this->assertEmpty($quote->getCustomerEmail(), 'Precondition failed: Customer data must not be set to quote');

        /** Execute SUT */
        $this->_checkoutSession->loadCustomerQuote();
        $quote = $this->_checkoutSession->getQuote();
        $this->_validateCustomerDataInQuote($quote);
    }

    /**
     * Ensure that quote has customer data specified in customer fixture.
     *
     * @param \Magento\Quote\Model\Quote $quote
     */
    protected function _validateCustomerDataInQuote($quote)
    {
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $customerFirstNameFromFixture = 'John';
        $this->assertEquals(
            $customerEmailFromFixture,
            $quote->getCustomerEmail(),
            'Customer email was not set to Quote correctly.'
        );
        $this->assertEquals(
            $customerIdFromFixture,
            $quote->getCustomerId(),
            'Customer ID was not set to Quote correctly.'
        );
        $this->assertEquals(
            $customerFirstNameFromFixture,
            $quote->getCustomerFirstname(),
            'Customer first name was not set to Quote correctly.'
        );
    }
}
