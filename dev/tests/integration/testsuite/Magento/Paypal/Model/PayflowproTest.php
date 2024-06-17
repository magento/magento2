<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class PayflowproTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Payflowpro
     */
    protected $_model;

    /**
     * @var LaminasClient
     */
    protected $_httpClientMock;

    /**
     * @var Gateway
     */
    protected $gatewayMock;

    protected function setUp(): void
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        $httpClientFactoryMock = $this->getMockBuilder(LaminasClientFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_httpClientMock = $this->getMockBuilder(LaminasClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'setUri',
                'setOptions',
                'setMethod',
                'setParameterPost',
                'setHeaders',
                'setUrlEncodeBody',
                'send'
            ])->getMock();
        $this->_httpClientMock->expects($this->any())->method('setUri')->willReturnSelf();
        $this->_httpClientMock->expects($this->any())->method('setOptions')->willReturnSelf();
        $this->_httpClientMock->expects($this->any())->method('setMethod')->willReturnSelf();
        $this->_httpClientMock->expects($this->any())->method('setParameterPost')->willReturnSelf();
        $this->_httpClientMock->expects($this->any())->method('setHeaders')->willReturnSelf();
        $this->_httpClientMock->expects($this->any())->method('setUrlEncodeBody')->willReturnSelf();

        $httpClientFactoryMock->expects($this->any())->method('create')
            ->willReturn($this->_httpClientMock);

        $mathRandomMock = $this->createMock(Random::class);
        $loggerMock = $this->createMock(Logger::class);
        $this->gatewayMock =$this->_objectManager->create(
            Gateway::class,
            [
                'httpClientFactory' => $httpClientFactoryMock,
                'mathRandom' => $mathRandomMock,
                'logger' => $loggerMock,
            ]
        );
        $this->_model = $this->_objectManager->create(
            Payflowpro::class,
            ['gateway' => $this->gatewayMock]
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_paid_with_payflowpro.php
     */
    public function testReviewPaymentNullResponce()
    {
        /** @var Order $order */
        $order = $this->_objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $this->_httpClientMock->expects($this->any())->method('send')
            ->willReturn(new DataObject(['body' => 'RESULTval=12&val2=34']));
        $expectedResult = ['resultval' => '12', 'val2' => '34', 'result_code' => null];

        $this->assertEquals($expectedResult, $this->_model->acceptPayment($order->getPayment()));
    }
}
