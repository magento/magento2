<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Apm;

use Laminas\Http\Exception\RuntimeException;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
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
     * @var LaminasClientFactory|MockObject
     */
    protected $httpClientFactoryMock;

    /**
     * @var LaminasClient|MockObject
     */
    protected $httpClientMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->httpClientFactoryMock = $this->createMock(LaminasClientFactory::class);
        $this->httpClientMock = $this->createMock(LaminasClient::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $this->model = new Deployments(
            $this->configMock,
            $this->loggerMock,
            $this->httpClientFactoryMock,
            $this->serializerMock
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

        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with($data['self_uri'])
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->with($data['method'])
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setHeaders')
            ->with($data['headers'])
            ->willReturnSelf();

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data['params'])
            ->willReturn(json_encode($data['params']));
        $this->httpClientMock->expects($this->once())
            ->method('setRawBody')
            ->with(json_encode($data['params']))
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn('');

        $this->loggerMock->expects($this->once())->method('notice');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $httpResponseMock = $this->getMockBuilder(
            Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $httpResponseMock->expects($this->any())->method('getStatusCode')->willReturn($data['status_ok']);
        $httpResponseMock->expects($this->once())->method('getBody')->willReturn($data['response_body']);

        $this->httpClientMock->expects($this->once())->method('send')->willReturn($httpResponseMock);

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->assertIsString(
            $this->model->setDeployment(
                $data['description'],
                $data['change'],
                $data['user'],
                $data['revision']
            )
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

        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with($data['uri'])
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->with($data['method'])
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setHeaders')
            ->with($data['headers'])
            ->willReturnSelf();

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data['params'])
            ->willReturn(json_encode($data['params']));
        $this->httpClientMock->expects($this->once())
            ->method('setRawBody')
            ->with(json_encode($data['params']))
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn($data['uri']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $httpResponseMock = $this->getMockBuilder(
            Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $httpResponseMock->expects($this->any())->method('getStatusCode')->willReturn($data['status_bad']);

        $this->httpClientMock->expects($this->once())->method('send')->willReturn($httpResponseMock);
        $this->loggerMock->expects($this->once())->method('warning');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->assertIsBool(
            $this->model->setDeployment(
                $data['description'],
                $data['change'],
                $data['user'],
                $data['revision']
            )
        );
    }

    /**
     * Tests client request will fail
     */
    public function testSetDeploymentRequestFail()
    {
        $data = $this->getDataVariables();

        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with($data['uri'])
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->with($data['method'])
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setHeaders')
            ->with($data['headers'])
            ->willReturnSelf();

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data['params'])
            ->willReturn(json_encode($data['params']));
        $this->httpClientMock->expects($this->once())
            ->method('setRawBody')
            ->with(json_encode($data['params']))
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn($data['uri']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $this->httpClientMock->expects($this->once())->method('send')->willThrowException(
            new RuntimeException()
        );
        $this->loggerMock->expects($this->once())->method('critical');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->assertIsBool(
            $this->model->setDeployment(
                $data['description'],
                $data['change'],
                $data['user'],
                $data['revision']
            )
        );
    }

    /**
     * @return array
     */
    private function getDataVariables(): array
    {
        $description = 'Event description';
        $change = 'flush the cache username';
        $user = 'username';
        $uri = 'https://example.com/listener';
        $selfUri = 'https://api.newrelic.com/v2/applications/%s/deployments.json';
        $apiKey = '1234';
        $appName = 'app_name';
        $appId = 'application_id';
        $method = Request::METHOD_POST;
        $headers = ['Api-Key' => $apiKey, 'Content-Type' => 'application/json'];
        $responseBody = 'Response body content';
        $statusOk = '200';
        $statusBad = '401';
        $revision = 'f81d42327219e17b1427096c354e9b8209939d4dd586972f12f0352f8343b91b';
        $params = [
            'deployment' => [
                'description' => $description,
                'changelog' => $change,
                'user' => $user,
                'revision' => $revision
            ]
        ];

        $selfUri = sprintf($selfUri, $appId);
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
            'params' => $params,
            'revision' => $revision
        ];
    }
}
