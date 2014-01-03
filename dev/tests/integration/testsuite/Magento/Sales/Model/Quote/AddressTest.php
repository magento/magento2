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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $address->setSameAsBilling(0)
            ->setCustomerAddress($this->_customer->getDefaultBillingAddress())
            ->save();
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
            ->setCustomerAddress(null)
            ->save();
        $this->assertEquals((int)$unsetId, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer registered and he has no default shipping address
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     */
    public function testSameAsBillingWhenCustomerHasNoDefaultShippingAddress($unsetId)
    {
        $this->_customer->setDefaultShipping(-1);
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave($unsetId);
        $this->assertEquals((int)$unsetId, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer has the same billing and shipping address
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
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
     */
    public function testSameAsBillingWhenCustomerHasDefaultShippingAddress($unsetId)
    {
        $this->_customer->setDefaultShipping(2);
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave($unsetId);
        $this->assertEquals(0, $this->_quote->getShippingAddress()->getSameAsBilling());
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
            ->setCustomerAddress($this->_customer->getDefaultBillingAddress())
            ->save();
    }

    public function unsetAddressIdDataProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }
}
