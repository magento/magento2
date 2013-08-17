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
 * @package     Mage_Checkout
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoAppArea frontend
 */
class Mage_Checkout_Model_Type_OnepageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Checkout/_files/quote_with_product_and_payment.php
     * @dataProvider saveOrderDataProvider
     *
     * @param array $customerData
     */
    public function testSaveOrder($customerData)
    {
        /** @var $model Mage_Checkout_Model_Type_Onepage */
        $model = Mage::getModel('Mage_Checkout_Model_Type_Onepage');

        /** @var Mage_Sales_Model_Resource_Quote_Collection $quoteCollection */
        $quoteCollection = Mage::getModel('Mage_Sales_Model_Resource_Quote_Collection');
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $quoteCollection->getFirstItem();

        $model->setQuote($quote);
        $model->saveBilling($customerData, null);

        $this->_prepareQuote($quote);

        $model->saveOrder();

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('Mage_Sales_Model_Order');
        $order->loadByIncrementId($model->getLastOrderId());

        $this->assertNotEmpty($quote->getShippingAddress()->getCustomerAddressId(),
            'Quote shipping CustomerAddressId should not be ampty');
        $this->assertNotEmpty($quote->getBillingAddress()->getCustomerAddressId(),
            'Quote billing CustomerAddressId should not be ampty');

        $this->assertNotEmpty($order->getShippingAddress()->getCustomerAddressId(),
            'Order shipping CustomerAddressId should not be ampty');
        $this->assertNotEmpty($order->getBillingAddress()->getCustomerAddressId(),
            'Order billing CustomerAddressId should not be ampty');
    }


    public function saveOrderDataProvider()
    {
        return array(
            array($this->_getCustomerData()),
        );
    }

    /**
     * Prepare Quote
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _prepareQuote($quote)
    {
        /** @var $rate Mage_Sales_Model_Quote_Address_Rate */
        $rate = Mage::getModel('Mage_Sales_Model_Quote_Address_Rate');
        $rate->setCode('freeshipping_freeshipping');
        $rate->getPrice(1);

        $quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');
        $quote->getShippingAddress()->addShippingRate($rate);
        $quote->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
    }

    /**
     * Customer data for quote
     *
     * @return array
     */
    protected function _getCustomerData()
    {
        return array (
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'John.Smith@example.com',
            'street' =>array (
                0 => '6131 Monterey Rd, Apt 1',
                1 => '',
            ),
            'city' => 'Los Angeles',
            'postcode' => '90042',
            'country_id' => 'AL',
            'telephone' => '(323) 255-5861',
            'customer_password' => 'password',
            'confirm_password' => 'password',
            'save_in_address_book' => '1',
            'use_for_shipping' => '1',
        );
    }
}
