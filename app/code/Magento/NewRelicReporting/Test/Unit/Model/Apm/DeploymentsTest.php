<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Apm;

use Magento\NewRelicReporting\Model\Apm\Deployments;
use \Magento\Framework\HTTP\ZendClient;

/**
 * Class DeploymentsTest
 */
class DeploymentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\NewRelicReporting\Model\Apm\Deployments
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $zendClientFactoryMock;

    /**
     * @var \Magento\Framework\HTTP\ZendClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $zendClientMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->zendClientFactoryMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClientFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->zendClientMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClient')
            ->setMethods(['request', 'setUri', 'setMethod', 'setHeaders', 'setParameterPost'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder('Magento\NewRelicReporting\Model\Config')
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

        $zendHttpResponseMock = $this->getMockBuilder('Zend_Http_Response')->disableOriginalConstructor()->getMock();
        $zendHttpResponseMock->expects($this->any())->method('getStatus')->willReturn($data['status_ok']);
        $zendHttpResponseMock->expects($this->once())->method('getBody')->willReturn($data['response_body']);

        $this->zendClientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);

        $this->assertInternalType(
            'string',
            $this->model->setDeployment($data['description'], $data['change'], $data['user'])
        );
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

        $zendHttpResponseMock = $this->getMockBuilder('Zend_Http_Response')->disableOriginalConstructor()->getMock();
        $zendHttpResponseMock->expects($this->any())->method('getStatus')->willReturn($data['status_bad']);

        $this->zendClientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);
        $this->loggerMock->expects($this->once())->method('warning');

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->zendClientMock);

        $this->assertInternalType(
            'bool',
            $this->model->setDeployment($data['description'], $data['change'], $data['user'])
        );
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

        $this->assertInternalType(
            'bool',
            $this->model->setDeployment($data['description'], $data['change'], $data['user'])
        );
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
