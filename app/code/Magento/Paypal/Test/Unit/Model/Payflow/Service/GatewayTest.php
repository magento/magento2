<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Math\Random;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Gateway;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    /** @var Gateway|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    /** @var ZendClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $httpClientFactoryMock;

    /** @var Random|\PHPUnit_Framework_MockObject_MockObject */
    protected $mathRandomMock;

    /** @var Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var ZendClient|\PHPUnit_Framework_MockObject_MockObject */
    protected $zendClientMock;

    protected function setUp()
    {
        $this->httpClientFactoryMock = $this->getMockBuilder('\Magento\Framework\HTTP\ZendClientFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->zendClientMock = $this->getMockBuilder('\Magento\Framework\HTTP\ZendClient')
            ->setMethods(['request', 'setUri'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);
        $this->mathRandomMock = $this->getMockBuilder('\Magento\Framework\Math\Random')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('\Magento\Payment\Model\Method\Logger')
            ->setConstructorArgs([$this->getMockForAbstractClass('Psr\Log\LoggerInterface')])
            ->setMethods(['debug'])
            ->getMock();

        $this->object = new Gateway(
            $this->httpClientFactoryMock,
            $this->mathRandomMock,
            $this->loggerMock
        );
    }

    public function testPostRequestOk()
    {
        $configInterfaceMock = $this->getMockBuilder('\Magento\Payment\Model\Method\ConfigInterface')
            ->getMockForAbstractClass();
        $zendResponseMock = $this->getMockBuilder('\Zend_Http_Response')
            ->setMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $zendResponseMock->expects($this->once())
            ->method('getBody')
            ->willReturn('RESULT=0&RESPMSG=Approved&SECURETOKEN=8ZIaw2&SECURETOKENID=2481d53');
        $this->zendClientMock->expects($this->once())
            ->method('request')
            ->willReturn($zendResponseMock);

        $configMap = [
            ['getDebugReplacePrivateDataKeys', null, ['masked']],
            ['debug', null, true]
        ];
        $configInterfaceMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($configMap));
        $this->loggerMock->expects($this->once())
            ->method('debug');

        $object = new \Magento\Framework\DataObject();

        $result = $this->object->postRequest($object, $configInterfaceMock);

        $this->assertInstanceOf('Magento\Framework\DataObject', $result);
        $this->assertArrayHasKey('result_code', $result->getData());
    }

    /**
     * @expectedException  \Exception
     */
    public function testPostRequestFail()
    {
        $configInterfaceMock = $this->getMockBuilder('\Magento\Payment\Model\Method\ConfigInterface')
            ->getMockForAbstractClass();
        $zendResponseMock = $this->getMockBuilder('\Zend_Http_Response')
            ->setMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $zendResponseMock->expects($this->never())
            ->method('getBody');
        $this->zendClientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception());

        $object = new \Magento\Framework\DataObject();
        $this->object->postRequest($object, $configInterfaceMock);
    }
}
