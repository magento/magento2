<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\NewRelicReporting\Model\CronEvent;
use \Magento\Framework\HTTP\ZendClient;

/**
 * Class CronEventTest
 */
class CronEventTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NewRelicReporting\Model\CronEvent
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $zendClientFactoryMock;

    /**
     * @var \Magento\Framework\HTTP\ZendClient|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $zendClientMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $jsonEncoderMock;

    protected function setUp(): void
    {
        $this->zendClientFactoryMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->zendClientMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClient::class)
            ->setMethods(['request', 'setUri', 'setMethod', 'setHeaders', 'setRawData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Config::class)
            ->setMethods([
                'getNewRelicAccountId',
                'getInsightsApiUrl',
                'getInsightsInsertKey',
                'getNewRelicAppName',
                'getNewRelicAppId'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CronEvent(
            $this->configMock,
            $this->jsonEncoderMock,
            $this->zendClientFactoryMock
        );
    }

    /**
     * Tests client request with Ok status
     *
     * @return void
     */
    public function testSendRequestStatusOk()
    {
        $json = '{"eventType":"Cron","appName":"app_name","appId":"app_id"}';
        $statusOk = '200';
        $uri = 'https://example.com/listener';
        $method = ZendClient::POST;
        $headers = ['X-Insert-Key' => 'insert_key_value', 'Content-Type' => 'application/json'];
        $accId = 'acc_id';
        $appId = 'app_id';
        $appName = 'app_name';
        $insightApiKey = 'insert_key_value';

        $this->model->addData(['eventType'=>'Cron']);

        $this->zendClientMock->expects($this->once())->method('setUri')->with($uri)->willReturnSelf();
        $this->zendClientMock->expects($this->once())->method('setMethod')->with($method)->willReturnSelf();
        $this->zendClientMock->expects($this->once())->method('setHeaders')->with($headers)->willReturnSelf();
        $this->zendClientMock->expects($this->once())->method('setRawData')->with($json)->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getNewRelicAccountId')
            ->willReturn($accId);

        $this->configMock->expects($this->once())
            ->method('getInsightsApiUrl')
            ->willReturn($uri);

        $this->configMock->expects($this->once())
            ->method('getInsightsInsertKey')
            ->willReturn($insightApiKey);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn($appName);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($appId);

        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturn($json);

        $zendHttpResponseMock = $this->getMockBuilder(
            \Zend_Http_Response::class
        )->disableOriginalConstructor()->getMock();
        $zendHttpResponseMock->expects($this->any())->method('getStatus')->willReturn($statusOk);

        $this->zendClientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);

        $this->assertIsBool($this->model->sendRequest()
        );
    }

    /**
     * Tests client request with Bad status
     *
     * @return void
     */
    public function testSendRequestStatusBad()
    {
        $json = '{"eventType":"Cron","appName":"app_name","appId":"app_id"}';
        $statusBad = '401';
        $uri = 'https://example.com/listener';
        $method = ZendClient::POST;
        $headers = ['X-Insert-Key' => 'insert_key_value', 'Content-Type' => 'application/json'];
        $accId = 'acc_id';
        $appId = 'app_id';
        $appName = 'app_name';
        $insightApiKey = 'insert_key_value';

        $this->zendClientMock->expects($this->once())->method('setUri')->with($uri)->willReturnSelf();
        $this->zendClientMock->expects($this->once())->method('setMethod')->with($method)->willReturnSelf();
        $this->zendClientMock->expects($this->once())->method('setHeaders')->with($headers)->willReturnSelf();
        $this->zendClientMock->expects($this->once())->method('setRawData')->with($json)->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getNewRelicAccountId')
            ->willReturn($accId);

        $this->configMock->expects($this->once())
            ->method('getInsightsApiUrl')
            ->willReturn($uri);

        $this->configMock->expects($this->once())
            ->method('getInsightsInsertKey')
            ->willReturn($insightApiKey);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn($appName);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($appId);

        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturn($json);

        $zendHttpResponseMock = $this->getMockBuilder(
            \Zend_Http_Response::class
        )->disableOriginalConstructor()->getMock();
        $zendHttpResponseMock->expects($this->any())->method('getStatus')->willReturn($statusBad);

        $this->zendClientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);

        $this->assertIsBool($this->model->sendRequest()
        );
    }

    /**
     * Tests client request with exception
     *
     * @return void
     */
    public function testSendRequestException()
    {
        $accId = '';

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);
        $this->configMock->expects($this->once())
            ->method('getNewRelicAccountId')
            ->willReturn($accId);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->model->sendRequest();
    }
}
