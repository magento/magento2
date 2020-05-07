<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Apm;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\NewRelicReporting\Model\Apm\Deployments;
use Magento\NewRelicReporting\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeploymentsTest extends TestCase
{
    /**
     * @var Deployments
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var ZendClientFactory|MockObject
     */
    protected $zendClientFactoryMock;

    /**
     * @var ZendClient|MockObject
     */
    protected $zendClientMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    protected function setUp(): void
    {
        $this->zendClientFactoryMock = $this->getMockBuilder(ZendClientFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->zendClientMock = $this->getMockBuilder(ZendClient::class)
            ->setMethods(['request', 'setUri', 'setMethod', 'setHeaders', 'setParameterPost'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getNewRelicApiUrl', 'getNewRelicApiKey', 'getNewRelicAppName', 'getNewRelicAppId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Deployments(
            $this->configMock,
            $this->loggerMock,
            $this->zendClientFactoryMock
        );
    }

    /**
     * Tests client request with Ok status
     *
     * @return void
     */
    public function testSetDeploymentRequestOk()
    {
        $data = $this->getDataVariables();

        $this->zendClientMock->expects($this->once())
            ->method('setUri')
            ->with($data['self_uri'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setMethod')
            ->with($data['method'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setHeaders')
            ->with($data['headers'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setParameterPost')
            ->with($data['params'])
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn('');

        $this->loggerMock->expects($this->once())->method('notice');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn($data['app_name']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $zendHttpResponseMock = $this->getMockBuilder(
            \Zend_Http_Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $zendHttpResponseMock->expects($this->any())->method('getStatus')->willReturn($data['status_ok']);
        $zendHttpResponseMock->expects($this->once())->method('getBody')->willReturn($data['response_body']);

        $this->zendClientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);

        $this->assertIsString($this->model->setDeployment($data['description'], $data['change'], $data['user']));
    }

    /**
     * Tests client request with bad status
     *
     * @return void
     */
    public function testSetDeploymentBadStatus()
    {
        $data = $this->getDataVariables();

        $this->zendClientMock->expects($this->once())
            ->method('setUri')
            ->with($data['uri'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setMethod')
            ->with($data['method'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setHeaders')
            ->with($data['headers'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setParameterPost')
            ->with($data['params'])
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn($data['uri']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn($data['app_name']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $zendHttpResponseMock = $this->getMockBuilder(
            \Zend_Http_Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $zendHttpResponseMock->expects($this->any())->method('getStatus')->willReturn($data['status_bad']);

        $this->zendClientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);
        $this->loggerMock->expects($this->once())->method('warning');

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);

        $this->assertIsBool($this->model->setDeployment($data['description'], $data['change'], $data['user']));
    }

    /**
     * Tests client request will fail
     */
    public function testSetDeploymentRequestFail()
    {
        $data = $this->getDataVariables();

        $this->zendClientMock->expects($this->once())
            ->method('setUri')
            ->with($data['uri'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setMethod')
            ->with($data['method'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setHeaders')
            ->with($data['headers'])
            ->willReturnSelf();

        $this->zendClientMock->expects($this->once())
            ->method('setParameterPost')
            ->with($data['params'])
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn($data['uri']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn($data['app_name']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $this->zendClientMock->expects($this->once())->method('request')->willThrowException(
            new \Zend_Http_Client_Exception()
        );
        $this->loggerMock->expects($this->once())->method('critical');

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);

        $this->assertIsBool($this->model->setDeployment($data['description'], $data['change'], $data['user']));
    }

    /**
     * @return array
     */
    private function getDataVariables()
    {
        $description = 'Event description';
        $change = 'flush the cache username';
        $user = 'username';
        $uri = 'https://example.com/listener';
        $selfUri = 'https://api.newrelic.com/deployments.xml';
        $apiKey = '1234';
        $appName = 'app_name';
        $appId = 'application_id';
        $method = ZendClient::POST;
        $headers = ['x-api-key' => $apiKey];
        $responseBody = 'Response body content';
        $statusOk = '200';
        $statusBad = '401';
        $params = [
            'deployment[app_name]'       => $appName,
            'deployment[application_id]' => $appId,
            'deployment[description]'    => $description,
            'deployment[changelog]'      => $change,
            'deployment[user]'           => $user
        ];

        return ['description' => $description,
            'change' => $change,
            'user' => $user,
            'uri' => $uri,
            'self_uri' => $selfUri,
            'api_key' => $apiKey,
            'app_name' => $appName,
            'app_id' => $appId,
            'method' => $method,
            'headers' => $headers,
            'status_ok' => $statusOk,
            'status_bad' => $statusBad,
            'response_body' => $responseBody,
            'params' => $params
        ];
    }
}
