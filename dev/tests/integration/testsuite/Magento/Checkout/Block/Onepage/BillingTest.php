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
 */
namespace Magento\Checkout\Block\Onepage;

use Magento\TestFramework\Helper\Bootstrap;

class BillingTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Block\Onepage\Billing */
    protected $_block;

    /** @var \Magento\Customer\Service\V1\CustomerAddressService */
    protected $_addressService;

    /** @var \Magento\Sales\Model\Quote\AddressFactory */
    protected $_quoteAddressFactory;

    /** @var  \Magento\Customer\Service\V1\Data\CustomerBuilder */
    protected $_customerBuilder;

    /** @var \Magento\Customer\Service\V1\CustomerAccountService */
    protected $_customerService;

    const FIXTURE_CUSTOMER_ID = 1;

    const FIXTURE_ADDRESS_ID = 1;

    const SAMPLE_FIRST_NAME = 'UpdatedFirstName';

    const SAMPLE_LAST_NAME = 'UpdatedLastName';

    protected function setUp()
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->_customerBuilder = $objectManager->create('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $this->_customerService = $objectManager->create('Magento\Customer\Service\V1\CustomerAccountService');
        $customerData = $this->_customerService->getCustomer(self::FIXTURE_CUSTOMER_ID);

        $customerSession = $objectManager->get('\Magento\Customer\Model\Session');
        $customerSession->setCustomerData($customerData);

        $this->_addressService = $objectManager->get('Magento\Customer\Service\V1\CustomerAddressService');
        //fetch sample address
        $address = $this->_addressService->getAddress(self::FIXTURE_ADDRESS_ID);

        /** @var \Magento\Sales\Model\Resource\Quote\Collection $quoteCollection */
        $quoteCollection = $objectManager->get('Magento\Sales\Model\Resource\Quote\Collection');
        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $quoteCollection->getLastItem();
        $quote->setCustomerData($customerData);
        /** @var $quoteAddressFactory \Magento\Sales\Model\Quote\AddressFactory */
        $this->_quoteAddressFactory = $objectManager->get('Magento\Sales\Model\Quote\AddressFactory');
        $billingAddress = $this->_quoteAddressFactory->create()->importCustomerAddressData($address);
        $quote->setBillingAddress($billingAddress);
        $quote->save();

        /** @var $checkoutSession \Magento\Checkout\Model\Session */
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        $checkoutSession->setQuoteId($quote->getId());
        $checkoutSession->setLoadInactive(true);

        $objectManager->get('Magento\Framework\App\Http\Context')
            ->setValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH, true, false);
        $this->_block = $objectManager->get('Magento\Framework\View\LayoutInterface')
            ->createBlock(
                'Magento\Checkout\Block\Onepage\Billing',
                '',
                ['customerSession' => $customerSession, 'checkoutSession' => $checkoutSession]
            );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_product_and_payment.php
     */
    public function testGetAddress()
    {
        $addressFromFixture = $this->_addressService->getAddress(self::FIXTURE_ADDRESS_ID);
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
        /** @var $emptyAddress \Magento\Sales\Model\Quote\Address */
        $emptyAddress = $this->_quoteAddressFactory->create();
        $emptyAddress->setFirstname(null);
        $emptyAddress->setLastname(null);
        $this->_block->getQuote()->setBillingAddress($emptyAddress);
        $customerData = $this->_customerService->getCustomer(self::FIXTURE_CUSTOMER_ID);
        $customerData = $this->_customerBuilder->populate(
            $customerData
        )->setFirstname(
            self::SAMPLE_FIRST_NAME
        )->setLastname(
            self::SAMPLE_LAST_NAME
        )->create();
        $this->_block->getQuote()->setCustomerData($customerData);
        $this->_block->getQuote()->save();

        $this->assertEquals(self::SAMPLE_FIRST_NAME, $this->_block->getFirstname());
        $this->assertEquals(self::SAMPLE_LAST_NAME, $this->_block->getLastname());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testGetAddressesHtmlSelect()
    {
        Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session')->setCustomerId(1);
        // @codingStandardsIgnoreStart
        $expected = <<<OUTPUT
<select name="billing_address_id" id="billing-address-select" class="address-select" title="" ><option value="1" selected="selected" >John Smith, Green str, 67, CityM, Alabama 75477, United States</option><option value="" >New Address</option></select>
OUTPUT;
        // @codingStandardsIgnoreEnd
        $this->assertEquals($expected, $this->_block->getAddressesHtmlSelect('billing'));
    }
}
