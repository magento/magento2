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
 * @package     Mage_Paypal
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Paypal_Model_IpnTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Paypal_Model_Ipn
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Paypal_Model_Ipn();
    }

    /**
     * @param string $currencyCode
     * @dataProvider currencyProvider
     * @magentoDataFixture Mage/Paypal/_files/order_express.php
     * @magentoConfigFixture current_store payment/paypal_direct/active 1
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store paypal/general/merchant_country US
     */
    public function testProcessIpnRequestExpressCurrency($currencyCode)
    {
        $this->_testProcessIpnRequestCurrency($currencyCode);
    }

    /**
     * @param string $currencyCode
     * @dataProvider currencyProvider
     * @magentoDataFixture Mage/Paypal/_files/order_standard.php
     * @magentoConfigFixture current_store payment/paypal_standard/active 1
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     */
    public function testProcessIpnRequestStandardCurrency($currencyCode)
    {
        $this->_testProcessIpnRequestCurrency($currencyCode);
    }

    /**
     * Test processIpnRequest() currency check for paypal_express and paypal_standard payment methods
     *
     * @param string $currencyCode
     * @dataProvider currencyProvider
     */
    protected function _testProcessIpnRequestCurrency($currencyCode)
    {
        $ipnData = require(__DIR__ . '/../_files/ipn.php');
        $ipnData['mc_currency'] = $currencyCode;

        $this->_model->processIpnRequest($ipnData, $this->_createMockedHttpAdapter());

        $order = new Mage_Sales_Model_Order();
        $order->loadByIncrementId('100000001');
        $this->_assertOrder($order, $currencyCode);
    }

    /**
     * Test processIpnRequest() currency check for recurring profile
     *
     * @param string $currencyCode
     * @dataProvider currencyProvider
     * @magentoDataFixture Mage/Paypal/_files/recurring_profile.php
     * @magentoConfigFixture current_store payment/paypal_direct/active 1
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store paypal/general/merchant_country US
     * @magentoConfigFixture current_store sales_email/order/enabled 0
     */
    public function testProcessIpnRequestRecurringCurrency($currencyCode)
    {
        $ipnData = require(__DIR__ . '/../_files/ipn_recurring_profile.php');
        $ipnData['mc_currency'] = $currencyCode;

        $this->_model->processIpnRequest($ipnData, $this->_createMockedHttpAdapter());

        $recurringProfile = new Mage_Sales_Model_Recurring_Profile();
        $recurringProfile->loadByInternalReferenceId('5-33949e201adc4b03fbbceafccba893ce');
        $orderIds = $recurringProfile->getChildOrderIds();
        $this->assertEquals(1, count($orderIds));
        $order = new Mage_Sales_Model_Order();
        $order->load($orderIds[0]);
        $this->_assertOrder($order, $currencyCode);
    }

    /**
     * Perform order state and status assertions depending on currency code
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $currencyCode
     */
    protected function _assertOrder($order, $currencyCode)
    {
        if ($currencyCode == 'USD') {
            $this->assertEquals('complete', $order->getState());
            $this->assertEquals('complete', $order->getStatus());
        } else {
            $this->assertEquals('payment_review', $order->getState());
            $this->assertEquals('fraud', $order->getStatus());
        }
    }

    /**
     * Data provider for currency check tests
     *
     * @static
     * @return array
     */
    public static function currencyProvider()
    {
        return array(
            array('USD'),
            array('EUR'),
        );
    }

    /**
     * Mocked HTTP adapter to get VERIFIED PayPal IPN postback result
     *
     * @return Varien_Http_Adapter_Curl
     */
    protected function _createMockedHttpAdapter()
    {
        $adapter = $this->getMock('Varien_Http_Adapter_Curl', array('read', 'write'));

        $adapter->expects($this->once())
            ->method('read')
            ->with()
            ->will($this->returnValue("\nVERIFIED"));

        $adapter->expects($this->once())
            ->method('write');

        return $adapter;
    }
}
