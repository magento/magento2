<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Elasticsearch8\Test\Unit\Model\Client;

use DG\BypassFinals;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Endpoints\Indices;
use Elastic\Elasticsearch\Response\Elasticsearch as ElasticsearchResponse;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\AddDefaultSearchField;
use Magento\Elasticsearch8\Model\Adapter\DynamicTemplates\IntegerMapper;
use Magento\Elasticsearch8\Model\Adapter\DynamicTemplates\PositionMapper;
use Magento\Elasticsearch8\Model\Adapter\DynamicTemplates\PriceMapper;
use Magento\Elasticsearch8\Model\Adapter\DynamicTemplates\StringMapper;
use Magento\Elasticsearch8\Model\Adapter\DynamicTemplatesProvider;
use Magento\Elasticsearch8\Model\Client\Elasticsearch;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ElasticsearchTest to test Elasticsearch 8
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ElasticsearchTest extends TestCase
{
    /**
     * @var Elasticsearch
     */
    private $model;

    /**
     * @var Client|MockObject
     */
    private $elasticsearchClientMock;

    /**
     * @var Indices|MockObject
     */
    private $indicesMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /** @var ElasticsearchResponse|MockObject */
    private $elasticsearchResponse;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        BypassFinals::enable();
        $this->elasticsearchClientMock = $this->getMockBuilder(Client::class) /** @phpstan-ignore-line */
        ->onlyMethods(
            [
                'indices',
                'ping',
                'bulk',
                'search',
                'scroll',
                'info',
            ]
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->indicesMock = $this->getMockBuilder(Indices::class) /** @phpstan-ignore-line */
        ->onlyMethods(
            [
                'exists',
                'getSettings',
                'create',
                'delete',
                'putMapping',
                'getMapping',
                'stats',
                'updateAliases',
                'existsAlias',
                'getAlias',
            ]
        )
            ->addMethods(['deleteMapping'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->elasticsearchResponse = $this->getMockBuilder(ElasticsearchResponse::class) /** @phpstan-ignore-line */
        ->onlyMethods([
            'asBool',
            'asArray',
        ])
            ->getMock();
        $this->elasticsearchClientMock->expects($this->any())
            ->method('indices')
            ->willReturn($this->indicesMock);
        $this->elasticsearchClientMock->expects($this->any())
            ->method('ping')
            ->willReturn($this->elasticsearchResponse);

        $this->objectManager = new ObjectManagerHelper($this);
        $dynamicTemplatesProvider = new DynamicTemplatesProvider(
            [
                new PriceMapper(),
                new PositionMapper(),
                new StringMapper(),
                new IntegerMapper(),
            ]
        );
        $this->model = $this->objectManager->getObject(
            Elasticsearch::class,
            [
                'options' => $this->getOptions(),
                'elasticsearchClient' => $this->elasticsearchClientMock,
                'fieldsMappingPreprocessors' => [new AddDefaultSearchField()],
                'dynamicTemplatesProvider' => $dynamicTemplatesProvider,
            ]
        );
    }

    /**
     * Test configurations with exception
     *
     * @return void
     */
    public function testConstructorOptionsException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $result = $this->objectManager->getObject(
            Elasticsearch::class,
            [
                'options' => [],
            ]
        );
        $this->assertNotNull($result);
    }

    /**
     * Test client creation from the list of options
     */
    public function testConstructorWithOptions()
    {
        $result = $this->objectManager->getObject(
            Elasticsearch::class,
            [
                'options' => $this->getOptions(),
            ]
        );
        $this->assertNotNull($result);
    }

    /**
     * Ensure that configuration returns correct url.
     *
     * @param array $options
     * @param string $expectedResult
     * @throws LocalizedException
     * @throws \ReflectionException
     * @dataProvider getOptionsDataProvider
     */
    public function testBuildConfig(array $options, string $expectedResult): void
    {
        $buildConfig = new Elasticsearch($options);
        $config = $this->getPrivateMethod();
        $result = $config->invoke($buildConfig, $options);
        $this->assertEquals($expectedResult, $result['hosts'][0]);
    }

    /**
     * Return private method for elastic search class.
     *
     * @return \ReflectionMethod
     */
    private function getPrivateMethod(): \ReflectionMethod
    {
        $reflector = new \ReflectionClass(Elasticsearch::class);
        $method = $reflector->getMethod('buildESConfig');
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Get options data provider.
     */
    public function getOptionsDataProvider(): array
    {
        return [
            [
                'without_protocol' => [
                    'hostname' => 'localhost',
                    'port' => '9200',
                    'timeout' => 15,
                    'index' => 'magento2',
                    'enableAuth' => 0,
                ],
                'expected_result' => 'http://localhost:9200',
            ],
            [
                'with_protocol' => [
                    'hostname' => 'https://localhost',
                    'port' => '9200',
                    'timeout' => 15,
                    'index' => 'magento2',
                    'enableAuth' => 0,
                ],
                'expected_result' => 'https://localhost:9200',
            ],
        ];
    }

    /**
     * Test ping functionality
     */
    public function testPing()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asBool')
            ->willReturn(true);
        $this->assertTrue($this->model->ping());
    }

    /**
     * Get elasticsearch client options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            'hostname' => 'localhost',
            'port' => '9200',
            'timeout' => 15,
            'index' => 'magento2',
            'enableAuth' => 1,
            'username' => 'user',
            'password' => 'passwd',
        ];
    }

    /**
     * Test validation of connection parameters
     */
    public function testTestConnection()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asBool')
            ->willReturn(true);
        $this->assertTrue($this->model->testConnection());
    }

    /**
     * Test validation of connection parameters returns false
     */
    public function testTestConnectionFalse()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asBool')
            ->willReturn(false);
        $this->assertFalse($this->model->testConnection());
    }

    /**
     * Test validation of connection parameters
     */
    public function testTestConnectionPing()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asBool')
            ->willReturn(true);
        $this->model = $this->objectManager->getObject(
            Elasticsearch::class,
            [
                'options' => $this->getEmptyIndexOption(),
                'elasticsearchClient' => $this->elasticsearchClientMock,
            ]
        );

        $this->model->ping();
        $this->assertTrue($this->model->testConnection());
    }

    /**
     * @return array
     */
    private function getEmptyIndexOption(): array
    {
        return [
            'hostname' => 'localhost',
            'port' => '9200',
            'index' => '',
            'timeout' => 15,
            'enableAuth' => 1,
            'username' => 'user',
            'password' => 'passwd',
        ];
    }

    /**
     * Test bulkQuery() method
     */
    public function testBulkQuery()
    {
        $this->elasticsearchClientMock->expects($this->once())
            ->method('bulk')
            ->with([]);
        $this->model->bulkQuery([]);
    }

    /**
     * Test createIndex() method, case when such index exists
     */
    public function testCreateIndexExists()
    {
        $this->indicesMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'index' => 'indexName',
                    'body' => [],
                ]
            );
        $this->model->createIndex('indexName', []);
    }

    /**
     * Test deleteIndex() method.
     */
    public function testDeleteIndex()
    {
        $this->indicesMock->expects($this->once())
            ->method('delete')
            ->with(['index' => 'indexName']);
        $this->model->deleteIndex('indexName');
    }

    /**
     * Test isEmptyIndex() method.
     */
    public function testIsEmptyIndex()
    {
        $indexName = 'magento2_test_index';
        $stats['indices'][$indexName]['primaries']['docs']['count'] = 0;

        $this->indicesMock->expects($this->once())
            ->method('stats')
            ->with(['index' => $indexName, 'metric' => 'docs'])
            ->willReturn($stats);
        $this->assertTrue($this->model->isEmptyIndex($indexName));
    }

    /**
     * Test isEmptyIndex() method returns false.
     */
    public function testIsEmptyIndexFalse()
    {
        $indexName = 'magento2_test_index';
        $stats['indices'][$indexName]['primaries']['docs']['count'] = 1;

        $this->indicesMock->expects($this->once())
            ->method('stats')
            ->with(['index' => $indexName, 'metric' => 'docs'])
            ->willReturn($stats);
        $this->assertFalse($this->model->isEmptyIndex($indexName));
    }

    /**
     * Test updateAlias() method with new index.
     */
    public function testUpdateAlias()
    {
        $alias = 'alias1';
        $index = 'index1';

        $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $index]];

        $this->indicesMock->expects($this->once())
            ->method('updateAliases')
            ->with($params);
        $this->model->updateAlias($alias, $index);
    }

    /**
     * Test updateAlias() method with new and old index.
     */
    public function testUpdateAliasRemoveOldIndex()
    {
        $alias = 'alias1';
        $newIndex = 'index1';
        $oldIndex = 'indexOld';

        $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $newIndex]];
        $params['body']['actions'][] = ['remove' => ['alias' => $alias, 'index' => $oldIndex]];

        $this->indicesMock->expects($this->once())
            ->method('updateAliases')
            ->with($params);
        $this->model->updateAlias($alias, $newIndex, $oldIndex);
    }

    /**
     * Test indexExists() method, case when no such index exists
     */
    public function testIndexExists()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asBool')
            ->willReturn(true);
        $this->indicesMock->expects($this->once())
            ->method('exists')
            ->with(['index' => 'indexName'])
            ->willReturn($this->elasticsearchResponse);
        $this->model->indexExists('indexName');
    }

    /**
     * Tests existsAlias() method checking for alias.
     */
    public function testExistsAlias()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asBool')
            ->willReturn(true);
        $alias = 'alias1';
        $params = ['name' => $alias];
        $this->indicesMock->expects($this->once())
            ->method('existsAlias')
            ->with($params)
            ->willReturn($this->elasticsearchResponse);
        $this->assertTrue($this->model->existsAlias($alias));
    }

    /**
     * Tests existsAlias() method checking for alias and index.
     */
    public function testExistsAliasWithIndex()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asBool')
            ->willReturn(true);
        $alias = 'alias1';
        $index = 'index1';
        $params = ['name' => $alias, 'index' => $index];
        $this->indicesMock->expects($this->once())
            ->method('existsAlias')
            ->with($params)
            ->willReturn($this->elasticsearchResponse);
        $this->assertTrue($this->model->existsAlias($alias, $index));
    }

    /**
     * Test getAlias() method.
     */
    public function testGetAlias()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asArray')
            ->willReturn([]);
        $alias = 'alias1';
        $params = ['name' => $alias];
        $this->indicesMock->expects($this->once())
            ->method('getAlias')
            ->with($params)
            ->willReturn($this->elasticsearchResponse);
        $this->assertEquals([], $this->model->getAlias($alias));
    }

    /**
     * Test createIndexIfNotExists() method, case when operation fails
     */
    public function testCreateIndexFailure()
    {
        $this->expectException('Exception');
        $this->indicesMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'index' => 'indexName',
                    'body' => [],
                ]
            )
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->createIndex('indexName', []);
    }

    /**
     * Test testAddFieldsMapping() method
     */
    public function testAddFieldsMapping()
    {
        $this->indicesMock->expects($this->once())
            ->method('putMapping')
            ->with(
                [
                    'index' => 'indexName',
                    'body' => [
                        'properties' => [
                            '_search' => [
                                'type' => 'text',
                            ],
                            'name' => [
                                'type' => 'text',
                            ],
                        ],
                        'dynamic_templates' => [
                            [
                                'price_mapping' => [
                                    'match' => 'price_*',
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'type' => 'double',
                                        'store' => true,
                                    ],
                                ],
                            ],
                            [
                                'position_mapping' => [
                                    'match' => 'position_*',
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'type' => 'integer',
                                        'index' => true,
                                    ],
                                ],
                            ],
                            [
                                'string_mapping' => [
                                    'match' => '*',
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'type' => 'text',
                                        'index' => true,
                                        'copy_to' => '_search',
                                    ],
                                ],
                            ],
                            [
                                'integer_mapping' => [
                                    'match_mapping_type' => 'long',
                                    'mapping' => [
                                        'type' => 'integer',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        $this->model->addFieldsMapping(
            [
                'name' => [
                    'type' => 'text',
                ],
            ],
            'indexName',
            'product'
        );
    }

    /**
     * Test testAddFieldsMapping() method
     */
    public function testAddFieldsMappingFailure()
    {
        $this->expectException('Exception');
        $this->indicesMock->expects($this->once())
            ->method('putMapping')
            ->with(
                [
                    'index' => 'indexName',
                    'body' => [
                        'properties' => [
                            '_search' => [
                                'type' => 'text',
                            ],
                            'name' => [
                                'type' => 'text',
                            ],
                        ],
                        'dynamic_templates' => [
                            [
                                'price_mapping' => [
                                    'match' => 'price_*',
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'type' => 'double',
                                        'store' => true,
                                    ],
                                ],
                            ],
                            [
                                'position_mapping' => [
                                    'match' => 'position_*',
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'type' => 'integer',
                                        'index' => true,
                                    ],
                                ],
                            ],
                            [
                                'string_mapping' => [
                                    'match' => '*',
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'type' => 'text',
                                        'index' => true,
                                        'copy_to' => '_search',
                                    ],
                                ],
                            ],
                            [
                                'integer_mapping' => [
                                    'match_mapping_type' => 'long',
                                    'mapping' => [
                                        'type' => 'integer',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            )
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->addFieldsMapping(
            [
                'name' => [
                    'type' => 'text',
                ],
            ],
            'indexName',
            'product'
        );
    }

    /**
     * Test get Elasticsearch mapping process.
     *
     * @return void
     */
    public function testGetMapping(): void
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asArray')
            ->willReturn([]);
        $params = ['index' => 'indexName'];
        $this->indicesMock->expects($this->once())
            ->method('getMapping')
            ->with($params)
            ->willReturn($this->elasticsearchResponse);

        $this->model->getMapping($params);
    }

    /**
     * Test query() method
     *
     * @return void
     */
    public function testQuery()
    {
        $this->elasticsearchResponse->expects($this->once())
            ->method('asArray')
            ->willReturn([]);
        $query = ['test phrase query'];
        $this->elasticsearchClientMock->expects($this->once())
            ->method('search')
            ->with($query)
            ->willReturn($this->elasticsearchResponse);
        $this->assertEquals([], $this->model->query($query));
    }
}
