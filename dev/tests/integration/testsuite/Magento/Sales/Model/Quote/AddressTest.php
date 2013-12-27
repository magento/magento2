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
 * @category    Magento
 * @package     Magento_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Model\Quote;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
 * @magentoDataFixture Magento/Sales/_files/quote.php
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Quote $quote */
    protected $_quote;

    /** @var \Magento\Customer\Model\Customer $customer */
    protected $_customer;

    /**
     * Initialize quote and customer fixtures
     */
    public function setUp()
    {
        $this->_quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Sales\Model\Quote');
        $this->_quote->load('test01', 'reserved_order_id');
        $this->_quote->setIsMultiShipping('0');

        /** @var \Magento\Customer\Model\Customer $customer */
        $this->_customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer');
        $this->_customer->load(1);
    }

    /**
     * same_as_billing must be equal 0 if billing address is being saved
     */
    public function testSameAsBillingForBillingAddress()
    {
        $this->_quote->setCustomer($this->_customer);
        $this->_quote->getBillingAddress()
            ->setSameAsBilling(0)
            ->setCustomerAddress($this->_customer->getDefaultBillingAddress())
            ->save();
        $this->assertEquals(0, $this->_quote->getBillingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer is guest
     */
    public function testSameAsBillingWhenCustomerIsGuest()
    {
        $shippingAddress = $this->_quote->getShippingAddress();
        $shippingAddress->setSameAsBilling(0);
        $shippingAddress->save();
        $this->assertEquals(1, $shippingAddress->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if quote address has no customer address
     */
    public function testSameAsBillingWhenQuoteAddressHasNoCustomerAddress()
    {
        $this->_quote->setCustomer($this->_customer);
        $this->_quote->getShippingAddress()
            ->setSameAsBilling(0)
            ->setCustomerAddress(null)
            ->save();
        $this->assertEquals(1, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer registered and he has no default shipping address
     */
    public function testSameAsBillingWhenCustomerHasNoDefaultShippingAddress()
    {
        $this->_customer->setDefaultShipping(-1);
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave();
        $this->assertEquals(1, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer has the same billing and shipping address
     */
    public function testSameAsBillingWhenCustomerHasBillingSameShipping()
    {
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave();
        $this->assertEquals(1, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 0 if customer has default shipping address that differs from default billing
     */
    public function testSameAsBillingWhenCustomerHasDefaultShippingAddress()
    {
        $this->_customer->setDefaultShipping(2);
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave();
        $this->assertEquals(0, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * Assign customer address to quote address and save quote address
     */
    protected function _setCustomerAddressAndSave()
    {
        $this->_quote->getShippingAddress()
            ->setSameAsBilling(0)
            ->setCustomerAddress($this->_customer->getDefaultBillingAddress())
            ->save();
    }
}
