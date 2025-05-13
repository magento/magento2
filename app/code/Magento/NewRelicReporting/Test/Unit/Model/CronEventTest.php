<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\CronEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronEventTest extends TestCase
{
    /**
     * @var CronEvent
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var LaminasClientFactory|MockObject
     */
    protected $httpClientFactoryMock;

    /**
     * @var LaminasClient|MockObject
     */
    protected $httpClientMock;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoderMock;

    protected function setUp(): void
    {
        $this->httpClientFactoryMock = $this->getMockBuilder(LaminasClientFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClientMock = $this->getMockBuilder(LaminasClient::class)
            ->onlyMethods(['send', 'setUri', 'setMethod', 'setHeaders', 'setRawBody'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods([
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
            $this->httpClientFactoryMock
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
        $method = Request::METHOD_POST;
        $headers = ['X-Insert-Key' => 'insert_key_value', 'Content-Type' => 'application/json'];
        $accId = 'acc_id';
        $appId = 'app_id';
        $appName = 'app_name';
        $insightApiKey = 'insert_key_value';

        $this->model->addData(['eventType'=>'Cron']);

        $this->httpClientMock->expects($this->once())->method('setUri')->with($uri)->willReturnSelf();
        $this->httpClientMock->expects($this->once())->method('setMethod')->with($method)->willReturnSelf();
        $this->httpClientMock->expects($this->once())->method('setHeaders')->with($headers)->willReturnSelf();
        $this->httpClientMock->expects($this->once())->method('setRawBody')->with($json)->willReturnSelf();

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

        $httpResponseMock = $this->getMockBuilder(
            Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $httpResponseMock->expects($this->any())->method('getStatusCode')->willReturn($statusOk);

        $this->httpClientMock->expects($this->once())->method('send')->willReturn($httpResponseMock);

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->assertIsBool($this->model->sendRequest());
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
        $method = Request::METHOD_POST;
        $headers = ['X-Insert-Key' => 'insert_key_value', 'Content-Type' => 'application/json'];
        $accId = 'acc_id';
        $appId = 'app_id';
        $appName = 'app_name';
        $insightApiKey = 'insert_key_value';

        $this->httpClientMock->expects($this->once())->method('setUri')->with($uri)->willReturnSelf();
        $this->httpClientMock->expects($this->once())->method('setMethod')->with($method)->willReturnSelf();
        $this->httpClientMock->expects($this->once())->method('setHeaders')->with($headers)->willReturnSelf();
        $this->httpClientMock->expects($this->once())->method('setRawBody')->with($json)->willReturnSelf();

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

        $httpResponseMock = $this->getMockBuilder(
            Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $httpResponseMock->expects($this->any())->method('getStatusCode')->willReturn($statusBad);

        $this->httpClientMock->expects($this->once())->method('send')->willReturn($httpResponseMock);

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->assertIsBool($this->model->sendRequest());
    }

    /**
     * Tests client request with exception
     *
     * @return void
     */
    public function testSendRequestException()
    {
        $accId = '';

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);
        $this->configMock->expects($this->once())
            ->method('getNewRelicAccountId')
            ->willReturn($accId);

        $this->expectException(LocalizedException::class);

        $this->model->sendRequest();
    }
}
