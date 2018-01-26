<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Paypal\Model\IpnFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;

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
     * Refund full order amount by Paypal Express IPN message service.
     *
     * @magentoDataFixture Magento/Paypal/_files/order_express_with_invoice_and_shipping.php
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store paypal/general/merchant_country US
     */
    public function testProcessIpnRequestFullRefund()
    {
        $ipnData = require __DIR__ . '/../_files/ipn_refund.php';
        $ipnFactory = $this->_objectManager->create(IpnFactory::class);
        $ipnModel = $ipnFactory->create(
            [
                'data' => $ipnData,
                'curlFactory' => $this->_createMockedHttpAdapter()
            ]
        );

        $ipnModel->processIpnRequest();

        $order = $this->_objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $creditmemoItems = $order->getCreditmemosCollection()->getItems();
        $creditmemo = current($creditmemoItems);

        $this->assertEquals(Order::STATE_CLOSED, $order->getState()) ;
        $this->assertEquals(1, count($creditmemoItems));
        $this->assertEquals(Creditmemo::STATE_REFUNDED, $creditmemo->getState());
        $this->assertEquals(10, $order->getSubtotalRefunded());
        $this->assertEquals(10, $order->getBaseSubtotalRefunded());
        $this->assertEquals(20, $order->getShippingRefunded());
        $this->assertEquals(20, $order->getBaseShippingRefunded());
        $this->assertEquals(30, $order->getTotalRefunded());
        $this->assertEquals(30, $order->getBaseTotalRefunded());
        $this->assertEquals(30, $order->getTotalOnlineRefunded());
        $this->assertEmpty($order->getTotalOfflineRefunded());
    }

    /**
     * Partial refund of order amount by Paypal Express IPN message service.
     *
     * @magentoDataFixture Magento/Paypal/_files/order_express_with_invoice_and_shipping.php
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store paypal/general/merchant_country US
     */
    public function testProcessIpnRequestPartialRefund()
    {
        $ipnData = require __DIR__ . '/../_files/ipn_refund.php';

        $refundAmount = -15;
        $ipnData['mc_gross'] = $refundAmount;

        $ipnFactory = $this->_objectManager->create(IpnFactory::class);
        $ipnModel = $ipnFactory->create(
            [
                'data' => $ipnData,
                'curlFactory' => $this->_createMockedHttpAdapter()
            ]
        );

        $ipnModel->processIpnRequest();

        $order = $this->_objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $creditmemoItems = $order->getCreditmemosCollection()->getItems();
        $comments = $order->load($order->getId())->getAllStatusHistory();
        $commentData = reset($comments);
        $commentOrigin = sprintf(
            'IPN "Refunded". Refund issued by merchant. Registered notification about refunded amount of $%d.00. '.
            'Transaction ID: "%s". Credit Memo has not been created. Please create offline Credit Memo.',
            abs($refundAmount),
            $ipnData['txn_id']
        );

        $this->assertEquals(Order::STATE_PROCESSING, $order->getState()) ;
        $this->assertEmpty(count($creditmemoItems));
        $this->assertEquals(1, count($comments));
        $this->assertEquals($commentOrigin, $commentData->getComment());
    }

    /**
     * Refund rest of order amount by Paypal Express IPN message service.
     *
     * @magentoDataFixture Magento/Paypal/_files/order_express_with_invoice_and_creditmemo.php
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store paypal/general/merchant_country US
     */
    public function testProcessIpnRequestRestRefund()
    {
        $ipnData = require __DIR__ . '/../_files/ipn_refund.php';

        $ipnFactory = $this->_objectManager->create(IpnFactory::class);
        $ipnModel = $ipnFactory->create(
            [
                'data' => $ipnData,
                'curlFactory' => $this->_createMockedHttpAdapter()
            ]
        );

        $ipnModel->processIpnRequest();

        $order = $this->_objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $creditmemoItems = $order->getCreditmemosCollection()->getItems();

        $this->assertEquals(Order::STATE_CLOSED, $order->getState()) ;
        $this->assertEquals(2, count($creditmemoItems));
        $this->assertEquals(10, $order->getSubtotalRefunded());
        $this->assertEquals(10, $order->getBaseSubtotalRefunded());
        $this->assertEquals(20, $order->getShippingRefunded());
        $this->assertEquals(20, $order->getBaseShippingRefunded());
        $this->assertEquals(30, $order->getTotalRefunded());
        $this->assertEquals(30, $order->getBaseTotalRefunded());
        $this->assertEquals(30, $order->getTotalOnlineRefunded());
        $this->assertEmpty($order->getTotalOfflineRefunded());
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
