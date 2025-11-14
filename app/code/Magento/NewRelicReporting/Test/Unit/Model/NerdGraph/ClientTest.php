<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\NerdGraph;

use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NerdGraph\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Laminas\Http\Response;
use Laminas\Http\Exception\RuntimeException;

/**
 * Test for NerdGraph Client
 */
class ClientTest extends TestCase
{
    /**
     * @var LaminasClientFactory|MockObject
     */
    private $httpClientFactoryMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LaminasClient|MockObject
     */
    private $httpClientMock;

    /**
     * @var Response|MockObject
     */
    private $responseMock;

    protected function setUp(): void
    {
        $this->httpClientFactoryMock = $this->createMock(LaminasClientFactory::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->httpClientMock = $this->createMock(LaminasClient::class);
        $this->responseMock = $this->createMock(Response::class);

        $this->client = new Client(
            $this->httpClientFactoryMock,
            $this->serializerMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    /**
     * Test successful GraphQL query execution
     */
    public function testQuerySuccess()
    {
        $query = 'query { actor { accounts { name } } }';
        $variables = ['limit' => 10];
        $expectedResponse = ['data' => ['actor' => ['accounts' => [['name' => 'Test Account']]]]];

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with(['query' => $query, 'variables' => $variables])
            ->willReturn('{"query":"...","variables":{"limit":10}}');

        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with('https://api.newrelic.com/graphql');

        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->with('POST');

        $this->httpClientMock->expects($this->once())
            ->method('setHeaders');

        $this->httpClientMock->expects($this->once())
            ->method('setRawBody')
            ->with('{"query":"...","variables":{"limit":10}}');

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn('{"data":{"actor":{"accounts":[{"name":"Test Account"}]}}}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"data":{"actor":{"accounts":[{"name":"Test Account"}]}}}')
            ->willReturn($expectedResponse);

        // The implementation doesn't log "query executed successfully"
        $result = $this->client->query($query, $variables);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Test query with empty variables
     */
    public function testQueryWithEmptyVariables()
    {
        $query = 'query { actor { accounts { name } } }';
        $expectedResponse = ['data' => ['actor' => ['accounts' => []]]];

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with(['query' => $query, 'variables' => new \stdClass()])
            ->willReturn('{"query":"...","variables":{}}');

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn('{"data":{"actor":{"accounts":[]}}}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($expectedResponse);

        $result = $this->client->query($query);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Test query when New Relic is disabled
     */
    public function testQueryWhenNewRelicDisabled()
    {
        $query = 'query { actor { accounts { name } } }';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('New Relic is not enabled');

        $this->client->query($query);
    }

    /**
     * Test query with HTTP client exception
     */
    public function testQueryWithHttpClientException()
    {
        $query = 'query { actor { accounts { name } } }';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willThrowException(new RuntimeException('Connection failed'));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('NerdGraph API request failed: Connection failed');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NerdGraph API request failed: Connection failed');

        $this->client->query($query);
    }

    /**
     * Test query with bad HTTP status
     */
    public function testQueryWithBadHttpStatus()
    {
        $query = 'query { actor { accounts { name } } }';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(401);

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn('{"errors":[{"message":"Invalid API key"}]}');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('NerdGraph API returned status 401: {"errors":[{"message":"Invalid API key"}]}');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NerdGraph API returned status 401: {"errors":[{"message":"Invalid API key"}]}');

        $this->client->query($query);
    }

    /**
     * Test query with GraphQL errors
     */
    public function testQueryWithGraphQLErrors()
    {
        $query = 'query { actor { invalidField } }';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $responseBody = [
            'errors' => [
                ['message' => 'Field "invalidField" doesn\'t exist on type "Actor"']
            ],
            'data' => null
        ];

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn('{"errors":[{"message":"Field invalidField doesn\'t exist"}],"data":null}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($responseBody);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('NerdGraph GraphQL errors: Field "invalidField" doesn\'t exist on type "Actor"');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NerdGraph GraphQL errors: Field "invalidField" doesn\'t exist on type "Actor"');

        $this->client->query($query);
    }

    /**
     * Test query with GraphQL errors without message
     */
    public function testQueryWithGraphQLErrorsWithoutMessage()
    {
        $query = 'query { actor { invalidField } }';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $responseBody = [
            'errors' => [
                ['code' => 'VALIDATION_ERROR']
            ],
            'data' => null
        ];

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn('{"errors":[{"code":"VALIDATION_ERROR"}],"data":null}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($responseBody);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('NerdGraph GraphQL errors: Unknown GraphQL error');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NerdGraph GraphQL errors: Unknown GraphQL error');

        $this->client->query($query);
    }

    /**
     * Test getEntityGuidFromApplication by name - success
     */
    public function testGetEntityGuidFromApplicationByNameSuccess()
    {
        $appName = 'My Application';
        $expectedGuid = 'GUID123456';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $responseBody = [
            'data' => [
                'actor' => [
                    'entitySearch' => [
                        'results' => [
                            'entities' => [
                                [
                                    'guid' => $expectedGuid,
                                    'name' => $appName,
                                    'reporting' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($responseBody));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($responseBody);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Using active entity: My Application (GUID: GUID123456)');

        $result = $this->client->getEntityGuidFromApplication($appName);

        $this->assertEquals($expectedGuid, $result);
    }

    /**
     * Test getEntityGuidFromApplication by ID - success
     */
    public function testGetEntityGuidFromApplicationByIdSuccess()
    {
        $appId = '123456789';
        $expectedGuid = 'GUID123456';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $responseBody = [
            'data' => [
                'actor' => [
                    'entitySearch' => [
                        'results' => [
                            'entities' => [
                                [
                                    'guid' => $expectedGuid,
                                    'applicationId' => (int)$appId,
                                    'name' => 'App for ID ' . $appId,
                                    'reporting' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($responseBody));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($responseBody);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Using active entity: App for ID 123456789 (GUID: GUID123456)');

        $result = $this->client->getEntityGuidFromApplication(null, $appId);

        $this->assertEquals($expectedGuid, $result);
    }

    /**
     * Test getEntityGuidFromApplication fallback to first entity when name doesn't match
     */
    public function testGetEntityGuidFromApplicationFallbackToFirst()
    {
        $appName = 'Non-existent App';
        $expectedGuid = 'GUID123456';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $responseBody = [
            'data' => [
                'actor' => [
                    'entitySearch' => [
                        'results' => [
                            'entities' => [
                                [
                                    'guid' => $expectedGuid,
                                    'name' => 'Different App',
                                    'reporting' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($responseBody));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($responseBody);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Fallback to first entity: Different App (GUID: GUID123456)');

        $result = $this->client->getEntityGuidFromApplication($appName);

        $this->assertEquals($expectedGuid, $result);
    }

    /**
     * Test getEntityGuidFromApplication when no entities found
     */
    public function testGetEntityGuidFromApplicationNoEntitiesFound()
    {
        $appName = 'My Application';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $responseBody = [
            'data' => [
                'actor' => [
                    'entitySearch' => [
                        'results' => [
                            'entities' => []
                        ]
                    ]
                ]
            ]
        ];

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($responseBody));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($responseBody);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('No entities found for search: type = \'APPLICATION\' AND name = \'My Application\'');

        $result = $this->client->getEntityGuidFromApplication($appName);

        $this->assertNull($result);
    }

    /**
     * Test getEntityGuidFromApplication with exception
     */
    public function testGetEntityGuidFromApplicationWithException()
    {
        $appName = 'My Application';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willThrowException(new RuntimeException('Connection failed'));

        // Expect at least one error call (actual implementation may call error multiple times)
        $this->loggerMock->expects($this->atLeastOnce())
            ->method('error');

        $result = $this->client->getEntityGuidFromApplication($appName);

        $this->assertNull($result);
    }

    /**
     * Test getEntityGuidFromApplication with quotes in name
     */
    public function testGetEntityGuidFromApplicationWithQuotesInName()
    {
        $appName = 'My "Special" Application';
        $expectedGuid = 'GUID123456';

        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn('{"query":"...","variables":{"query":"..."}}');

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $responseBody = [
            'data' => [
                'actor' => [
                    'entitySearch' => [
                        'results' => [
                            'entities' => [
                                [
                                    'guid' => $expectedGuid,
                                    'name' => $appName,
                                    'reporting' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($responseBody));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($responseBody);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Using active entity: My "Special" Application (GUID: GUID123456)');

        $result = $this->client->getEntityGuidFromApplication($appName);

        $this->assertEquals($expectedGuid, $result);
    }

    /**
     * Test getEntityGuidFromApplication with no parameters
     */
    public function testGetEntityGuidFromApplicationWithNoParameters()
    {
        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getNerdGraphUrl')
            ->willReturn('https://api.newrelic.com/graphql');

        $this->configMock->expects($this->once())
            ->method('getNewRelicApiKey')
            ->willReturn('test-api-key');

        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);

        $this->httpClientMock->expects($this->once())
            ->method('send')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        // Empty entities response
        $responseBody = [
            'data' => [
                'actor' => [
                    'entitySearch' => [
                        'results' => [
                            'entities' => []
                        ]
                    ]
                ]
            ]
        ];

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($responseBody));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($responseBody);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('No entities found for search: type = \'APPLICATION\'');

        $result = $this->client->getEntityGuidFromApplication();

        $this->assertNull($result);
    }
}
