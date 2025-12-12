<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\NewRelicReporting\Model\NerdGraph\DeploymentTracker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;

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

    /**
     * @var DeploymentTracker|MockObject
     */
    private $deploymentTrackerMock;

    protected function setUp(): void
    {
        $this->httpClientFactoryMock = $this->createMock(LaminasClientFactory::class);
        $this->httpClientMock = $this->createMock(LaminasClient::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->deploymentTrackerMock = $this->createMock(DeploymentTracker::class);

        $this->model = new Deployments(
            $this->configMock,
            $this->loggerMock,
            $this->httpClientFactoryMock,
            $this->serializerMock,
            $this->deploymentTrackerMock
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
            ->method('getApiMode')
            ->willReturn('v2_rest');

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
            ->method('getApiMode')
            ->willReturn('v2_rest');

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
            ->method('getApiMode')
            ->willReturn('v2_rest');

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
     * Tests NerdGraph deployment creation with enhanced parameters
     *
     * @return void
     */
    public function testSetDeploymentNerdGraphMode()
    {
        $description = 'NerdGraph deployment test';
        $changelog = 'Enhanced changelog';
        $user = 'nerdgraph_user';
        $revision = 'v2.0.0';
        $commit = 'abc123';
        $deepLink = 'https://github.com/test/releases/v2.0.0';
        $groupId = 'staging';

        $expectedNerdGraphResponse = [
            'deploymentId' => '12345678-1234-1234-1234-123456789012',
            'entityGuid' => 'TEST_ENTITY_GUID',
            'version' => $revision,
            'description' => $description,
            'change_log' => $changelog,
            'user' => $user,
            'commit' => $commit,
            'deepLink' => $deepLink,
            'groupId' => $groupId,
            'timestamp' => 1234567890000
        ];

        // Mock config to return NerdGraph mode
        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('nerdgraph');

        // Mock DeploymentTracker to be called with correct parameters
        $this->deploymentTrackerMock->expects($this->once())
            ->method('setDeployment')
            ->with($description, $changelog, $user, $revision, $commit, $deepLink, $groupId)
            ->willReturn($expectedNerdGraphResponse);

        $result = $this->model->setDeployment(
            $description,
            $changelog,
            $user,
            $revision,
            $commit,
            $deepLink,
            $groupId
        );

        $this->assertIsArray($result);
        $this->assertEquals($expectedNerdGraphResponse, $result);
        $this->assertArrayHasKey('deploymentId', $result);
        $this->assertArrayHasKey('entityGuid', $result);
        $this->assertArrayHasKey('commit', $result);
        $this->assertArrayHasKey('deepLink', $result);
        $this->assertArrayHasKey('groupId', $result);
    }

    /**
     * Tests NerdGraph deployment creation failure
     *
     * @return void
     */
    public function testSetDeploymentNerdGraphModeFailure()
    {
        $description = 'Failed NerdGraph deployment';
        $change = 'Test changelog';
        $user = 'test_user';
        $revision = 'v1.0.0';

        // Mock config to return NerdGraph mode
        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('nerdgraph');

        // Mock DeploymentTracker to return false (failure)
        $this->deploymentTrackerMock->expects($this->once())
            ->method('setDeployment')
            ->with($description, $change, $user, $revision, null, null, null)
            ->willReturn(false);

        $result = $this->model->setDeployment(
            $description,
            $change,
            $user,
            $revision
        );

        $this->assertFalse($result);
    }

    /**
     * Tests mode detection for NerdGraph vs v2 REST
     *
     * @return void
     */
    public function testSetDeploymentModeDetectionNerdGraph()
    {
        $description = 'NerdGraph mode detection test';
        $changelog = 'Test changelog';
        $user = 'test_user';
        $revision = 'v1.0.0';

        // Test NerdGraph mode detection
        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('nerdgraph');

        $expectedResult = ['deploymentId' => 'test-123', 'entityGuid' => 'test-guid'];
        $this->deploymentTrackerMock->expects($this->once())
            ->method('setDeployment')
            ->with($description, $changelog, $user, $revision, null, null, null)
            ->willReturn($expectedResult);

        $result = $this->model->setDeployment($description, $changelog, $user, $revision);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests that enhanced parameters are properly passed to NerdGraph
     *
     * @return void
     */
    public function testSetDeploymentNerdGraphEnhancedParameters()
    {
        $description = 'Enhanced parameters test';
        $changelog = 'changelog';
        $user = 'test_user';
        $revision = 'v2.1.0';
        $commit = 'def456';
        $deepLink = 'https://example.com/deploy';
        $groupId = 'production';

        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('nerdgraph');

        // Verify all enhanced parameters are passed correctly
        $this->deploymentTrackerMock->expects($this->once())
            ->method('setDeployment')
            ->with(
                $this->equalTo($description),
                $this->equalTo($changelog),
                $this->equalTo($user),
                $this->equalTo($revision),
                $this->equalTo($commit),
                $this->equalTo($deepLink),
                $this->equalTo($groupId)
            )
            ->willReturn([
                'deploymentId' => 'enhanced-test',
                'commit' => $commit,
                'deepLink' => $deepLink,
                'groupId' => $groupId
            ]);

        $result = $this->model->setDeployment(
            $description,
            $changelog,
            $user,
            $revision,
            $commit,
            $deepLink,
            $groupId
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('commit', $result);
        $this->assertArrayHasKey('deepLink', $result);
        $this->assertArrayHasKey('groupId', $result);
        $this->assertEquals($commit, $result['commit']);
        $this->assertEquals($deepLink, $result['deepLink']);
        $this->assertEquals($groupId, $result['groupId']);
    }

    /**
     * Tests revision generation when null is passed
     *
     * @return void
     */
    public function testSetDeploymentWithNullRevision()
    {
        $data = $this->getDataVariables();

        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('v2_rest');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn($data['uri']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setHeaders')
            ->willReturnSelf();

        // Capture the serialized data to verify revision was generated
        $capturedParams = null;
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(function ($params) use (&$capturedParams) {
                $capturedParams = $params;
                return json_encode($params);
            });

        $this->httpClientMock->expects($this->once())
            ->method('setRawBody')
            ->willReturnSelf();

        $httpResponseMock = $this->createMock(Response::class);
        $httpResponseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);
        $httpResponseMock->expects($this->once())
            ->method('getBody')
            ->willReturn('success');

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($httpResponseMock);

        // Test with null revision
        $result = $this->model->setDeployment(
            $data['description'],
            $data['change'],
            $data['user'],
            null  // null revision should trigger generation
        );

        $this->assertIsString($result);

        // Verify that a revision was generated (should be a hash)
        $this->assertNotNull($capturedParams['deployment']['revision']);
        $this->assertIsString($capturedParams['deployment']['revision']);
        $this->assertEquals(64, strlen($capturedParams['deployment']['revision'])); // SHA256 hash length
    }

    /**
     * Tests status code boundary conditions (200-210 range)
     *
     * @param int $statusCode
     * @param bool $expectedSuccess
     * @return void
     */
    #[DataProvider('statusCodeBoundaryProvider')]
    public function testSetDeploymentStatusCodeBoundaries($statusCode, $expectedSuccess)
    {
        $data = $this->getDataVariables();

        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('v2_rest');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn($data['uri']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setHeaders')
            ->willReturnSelf();

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn(json_encode($data['params']));

        $this->httpClientMock->expects($this->once())
            ->method('setRawBody')
            ->willReturnSelf();

        $httpResponseMock = $this->createMock(Response::class);
        $httpResponseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn($statusCode);

        if ($expectedSuccess) {
            $httpResponseMock->expects($this->once())
                ->method('getBody')
                ->willReturn('success');
        } else {
            $this->loggerMock->expects($this->once())
                ->method('warning');
        }

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($httpResponseMock);

        $result = $this->model->setDeployment(
            $data['description'],
            $data['change'],
            $data['user'],
            $data['revision']
        );

        if ($expectedSuccess) {
            $this->assertIsString($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * Data provider for status code boundary testing
     *
     * @return array
     */
    public static function statusCodeBoundaryProvider(): array
    {
        return [
            'Status 199 (just below valid range)' => [199, false],
            'Status 200 (valid start)' => [200, true],
            'Status 201 (valid middle)' => [201, true],
            'Status 210 (valid end)' => [210, true],
            'Status 211 (just above valid range)' => [211, false],
            'Status 300 (redirect)' => [300, false],
            'Status 404 (not found)' => [404, false],
            'Status 500 (server error)' => [500, false]
        ];
    }

    /**
     * Tests NerdGraph with null/empty parameter casting
     *
     * @return void
     */
    public function testSetDeploymentNerdGraphParameterCasting()
    {
        $description = 'Parameter casting test';
        $changelog = '';    // Empty string should become null
        $user = '0';        // String '0' should become null (falsy)
        $revision = 'v1.0.0';

        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('nerdgraph');

        // Verify that falsy string parameters are cast to null correctly
        $this->deploymentTrackerMock->expects($this->once())
            ->method('setDeployment')
            ->with(
                $description,
                null,    // empty string should become null
                null,    // '0' should become null (falsy)
                $revision,
                null,
                null,
                null
            )
            ->willReturn(['deploymentId' => 'test-cast']);

        $result = $this->model->setDeployment(
            $description,
            $changelog,
            $user,
            $revision
        );

        $this->assertIsArray($result);
        $this->assertEquals('test-cast', $result['deploymentId']);
    }

    /**
     * Tests NerdGraph with truthy string parameters
     *
     * @return void
     */
    public function testSetDeploymentNerdGraphTruthyStrings()
    {
        $description = 'Truthy strings test';
        $changelog = 'actual changelog';  // Truthy string
        $user = 'actual user';           // Truthy string
        $revision = 'v1.0.0';

        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('nerdgraph');

        // Verify that truthy strings are passed as-is (cast to string)
        $this->deploymentTrackerMock->expects($this->once())
            ->method('setDeployment')
            ->with(
                $description,
                'actual changelog',  // Should be cast to string
                'actual user',       // Should be cast to string
                $revision,
                null,
                null,
                null
            )
            ->willReturn(['deploymentId' => 'test-truthy']);

        $result = $this->model->setDeployment(
            $description,
            $changelog,
            $user,
            $revision
        );

        $this->assertIsArray($result);
        $this->assertEquals('test-truthy', $result['deploymentId']);
    }

    /**
     * Tests NerdGraph with explicitly null parameters
     *
     * @return void
     */
    public function testSetDeploymentNerdGraphNullParameters()
    {
        $description = 'Null parameters test';
        $changelog = null;  // Explicit null
        $user = null;       // Explicit null
        $revision = 'v1.0.0';

        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('nerdgraph');

        // Verify that null parameters are passed through as null
        $this->deploymentTrackerMock->expects($this->once())
            ->method('setDeployment')
            ->with(
                $description,
                null,    // null should remain null
                null,    // null should remain null
                $revision,
                null,
                null,
                null
            )
            ->willReturn(['deploymentId' => 'test-null']);

        $result = $this->model->setDeployment(
            $description,
            $changelog,
            $user,
            $revision
        );

        $this->assertIsArray($result);
        $this->assertEquals('test-null', $result['deploymentId']);
    }

    /**
     * Tests explicit fallback URL usage when config URL is empty
     *
     * @return void
     */
    public function testSetDeploymentEmptyApiUrlFallback()
    {
        $data = $this->getDataVariables();

        $this->configMock->expects($this->once())
            ->method('getApiMode')
            ->willReturn('v2_rest');

        // Explicitly test empty string URL
        $this->configMock->expects($this->once())
            ->method('getNewRelicApiUrl')
            ->willReturn('');

        // Should log the fallback notice
        $this->loggerMock->expects($this->once())
            ->method('notice')
            ->with('New Relic API URL is blank, using fallback URL');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn($data['api_key']);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($data['app_id']);

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        // Verify the fallback URL is used (with app_id substitution)
        $expectedFallbackUrl = sprintf('https://api.newrelic.com/v2/applications/%s/deployments.json', $data['app_id']);
        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with($expectedFallbackUrl)
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->willReturnSelf();

        $this->httpClientMock->expects($this->once())
            ->method('setHeaders')
            ->willReturnSelf();

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn(json_encode($data['params']));

        $this->httpClientMock->expects($this->once())
            ->method('setRawBody')
            ->willReturnSelf();

        $httpResponseMock = $this->createMock(Response::class);
        $httpResponseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);
        $httpResponseMock->expects($this->once())
            ->method('getBody')
            ->willReturn('fallback success');

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($httpResponseMock);

        $result = $this->model->setDeployment(
            $data['description'],
            $data['change'],
            $data['user'],
            $data['revision']
        );

        $this->assertEquals('fallback success', $result);
    }

    /**
     * @return array
     */
    private function getDataVariables(): array
    {
        $description = 'Event description';
        $changelog = 'flush the cache username';
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
                'changelog' => $changelog,
                'user' => $user,
                'revision' => $revision
            ]
        ];

        $selfUri = sprintf($selfUri, $appId);
        return ['description' => $description,
            'change' => $changelog,
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
