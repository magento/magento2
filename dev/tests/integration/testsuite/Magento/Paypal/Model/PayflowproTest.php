<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model;

class PayflowproTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Paypal\Model\Payflowpro
     */
    protected $_model;

    /**
     * @var \Magento\Framework\HTTP\ZendClient
     */
    protected $_httpClientMock;

    /**
     * @var \Magento\Paypal\Model\Payflow\Service\Gateway
     */
    protected $gatewayMock;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $httpClientFactoryMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_httpClientMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClient::class)->setMethods([])
            ->disableOriginalConstructor()->getMock();
        $this->_httpClientMock->expects($this->any())->method('setUri')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setConfig')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setMethod')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setParameterPost')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setHeaders')->will($this->returnSelf());
        $this->_httpClientMock->expects($this->any())->method('setUrlEncodeBody')->will($this->returnSelf());

        $httpClientFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($this->_httpClientMock));

        $mathRandomMock = $this->createMock(\Magento\Framework\Math\Random::class);
        $loggerMock = $this->createMock(\Magento\Payment\Model\Method\Logger::class);
        $this->gatewayMock =$this->_objectManager->create(
            \Magento\Paypal\Model\Payflow\Service\Gateway::class,
            [
                'httpClientFactory' => $httpClientFactoryMock,
                'mathRandom' => $mathRandomMock,
                'logger' => $loggerMock,
            ]
        );
        $this->_model = $this->_objectManager->create(
            \Magento\Paypal\Model\Payflowpro::class,
            ['gateway' => $this->gatewayMock]
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_paid_with_payflowpro.php
     */
    public function testReviewPaymentNullResponce()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $this->_httpClientMock->expects($this->any())->method('request')
            ->will($this->returnValue(new \Magento\Framework\DataObject(['body' => 'RESULTval=12&val2=34'])));
        $expectedResult = ['resultval' => '12', 'val2' => '34', 'result_code' => null];

        $this->assertEquals($expectedResult, $this->_model->acceptPayment($order->getPayment()));
    }
}
