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
        /** Preconditions */
        $customerIdFromFixture = 1;
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService */
        $customerService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $customer = $customerService->getCustomer($customerIdFromFixture);
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
        /** Preconditions */
        $customerIdFromFixture = 1;
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService */
        $customerService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $customer = $customerService->getCustomer($customerIdFromFixture);
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
        /** Preconditions */
        $customerIdFromFixture = 1;
        $quote = $this->_checkoutSession->getQuote();
        $this->assertEmpty($quote->getCustomerId(), 'Precondition failed: Customer data must not be set to quote');
        $this->assertEmpty($quote->getCustomerEmail(), 'Precondition failed: Customer data must not be set to quote');

        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService */
        $customerService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $customer = $customerService->getCustomer($customerIdFromFixture);
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
     * @param \Magento\Sales\Model\Quote $quote
     */
    protected function _validateCustomerDataInQuote($quote)
    {
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $customerFirstNameFromFixture = 'Firstname';
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
