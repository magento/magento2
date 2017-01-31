<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

/**
 * @magentoAppArea frontend
 */
class IpnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @param string $currencyCode
     * @dataProvider currencyProvider
     * @magentoDataFixture Magento/Paypal/_files/order_express.php
     * @magentoConfigFixture current_store payment/paypal_direct/active 1
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store paypal/general/merchant_country US
     */
    public function testProcessIpnRequestExpressCurrency($currencyCode)
    {
        $this->_processIpnRequestCurrency($currencyCode);
    }

    /**
     * Test processIpnRequest() currency check for paypal_express and paypal_standard payment methods
     *
     * @param string $currencyCode
     */
    protected function _processIpnRequestCurrency($currencyCode)
    {
        $ipnData = require __DIR__ . '/../_files/ipn.php';
        $ipnData['mc_currency'] = $currencyCode;

        /** @var  $ipnFactory \Magento\Paypal\Model\IpnFactory */
        $ipnFactory = $this->_objectManager->create('Magento\Paypal\Model\IpnFactory');

        $model = $ipnFactory->create(['data' => $ipnData, 'curlFactory' => $this->_createMockedHttpAdapter()]);
        $model->processIpnRequest();

        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $this->_assertOrder($order, $currencyCode);
    }

    /**
     * Perform order state and status assertions depending on currency code
     *
     * @param \Magento\Sales\Model\Order $order
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
        return [['USD'], ['EUR']];
    }

    /**
     * Mocked HTTP adapter to get VERIFIED PayPal IPN postback result
     *
     * @return \Magento\Framework\HTTP\Adapter\Curl
     */
    protected function _createMockedHttpAdapter()
    {
        $factory = $this->getMock('Magento\Framework\HTTP\Adapter\CurlFactory', ['create'], [], '', false);
        $adapter = $this->getMock('Magento\Framework\HTTP\Adapter\Curl', ['read', 'write'], [], '', false);

        $adapter->expects($this->once())->method('read')->with()->will($this->returnValue("\nVERIFIED"));

        $adapter->expects($this->once())->method('write');

        $factory->expects($this->once())->method('create')->with()->will($this->returnValue($adapter));
        return $factory;
    }
}
