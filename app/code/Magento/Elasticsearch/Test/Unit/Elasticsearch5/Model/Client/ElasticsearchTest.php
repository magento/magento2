<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Elasticsearch5\Model\Client;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Magento\Elasticsearch\Elasticsearch5\Model\Client\Elasticsearch;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test elasticsearch client methods.
 */
class ElasticsearchTest extends TestCase
{
    /**
     * @var Elasticsearch
     */
    protected $model;

    /**
     * @var Client|MockObject
     */
    protected $elasticsearchClientMock;

    /**
     * @var IndicesNamespace|MockObject
     */
    protected $indicesMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->elasticsearchClientMock = $this->getMockBuilder(Client::class)
            ->setMethods(
                [
                    'indices',
                    'ping',
                    'bulk',
                    'search',
                    'scroll',
                    'suggest',
                    'info',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->setMethods(
                [
                    'exists',
                    'getSettings',
                    'create',
                    'delete',
                    'putMapping',
                    'deleteMapping',
                    'stats',
                    'updateAliases',
                    'existsAlias',
                    'getAlias',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->elasticsearchClientMock->expects($this->any())
            ->method('indices')
            ->willReturn($this->indicesMock);
        $this->elasticsearchClientMock->expects($this->any())
            ->method('ping')
            ->willReturn(true);
        $this->elasticsearchClientMock->expects($this->any())
            ->method('info')
            ->willReturn(['version' => ['number' => '5.0.0']]);

        $this->objectManager = new ObjectManagerHelper($this);
        $this->model = $this->objectManager->getObject(
            Elasticsearch::class,
            [
                'options' => $this->getOptions(),
                'elasticsearchClient' => $this->elasticsearchClientMock
            ]
        );
    }

    /**
     * Test ping functionality
     */
    public function testPing()
    {
        $this->elasticsearchClientMock->expects($this->once())->method('ping')->willReturn(true);
        $this->assertTrue($this->model->ping());
    }

    /**
     * Test validation of connection parameters
     */
    public function testTestConnection()
    {
        $this->elasticsearchClientMock->expects($this->once())->method('ping')->willReturn(true);
        $this->assertTrue($this->model->testConnection());
    }

    /**
     * Test validation of connection parameters returns false
     */
    public function testTestConnectionFalse()
    {
        $this->elasticsearchClientMock->expects($this->once())->method('ping')->willReturn(false);
        $this->assertTrue($this->model->testConnection());
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
        $indexName = 'magento2_index';
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
        $indexName = 'magento2_index';
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

        $params['body']['actions'][] = ['remove' => ['alias' => $alias, 'index' => $oldIndex]];
        $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $newIndex]];

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
        $this->indicesMock->expects($this->once())
            ->method('exists')
            ->with(['index' => 'indexName'])
            ->willReturn(true);
        $this->model->indexExists('indexName');
    }

    /**
     * Tests existsAlias() method checking for alias.
     */
    public function testExistsAlias()
    {
        $alias = 'alias1';
        $params = ['name' => $alias];
        $this->indicesMock->expects($this->once())
            ->method('existsAlias')
            ->with($params)
            ->willReturn(true);
        $this->assertTrue($this->model->existsAlias($alias));
    }

    /**
     * Tests existsAlias() method checking for alias and index.
     */
    public function testExistsAliasWithIndex()
    {
        $alias = 'alias1';
        $index = 'index1';
        $params = ['name' => $alias, 'index' => $index];
        $this->indicesMock->expects($this->once())
            ->method('existsAlias')
            ->with($params)
            ->willReturn(true);
        $this->assertTrue($this->model->existsAlias($alias, $index));
    }

    /**
     * Test getAlias() method.
     */
    public function testGetAlias()
    {
        $alias = 'alias1';
        $params = ['name' => $alias];
        $this->indicesMock->expects($this->once())
            ->method('getAlias')
            ->with($params)
            ->willReturn([]);
        $this->assertEquals([], $this->model->getAlias($alias));
    }

    /**
     * Test createIndexIfNotExists() method, case when operation fails
     */
    public function testCreateIndexFailure()
    {
        $this->expectException(\Exception::class);

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
                    'type' => 'product',
                    'body' => [
                        'product' => [
                            '_all' => [
                                'enabled' => true,
                                'type' => 'text',
                            ],
                            'properties' => [
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
                                            'type' => 'float',
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
                                            'index' => true
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
                                        ],
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
        $this->expectException(\Exception::class);

        $this->indicesMock->expects($this->once())
            ->method('putMapping')
            ->with(
                [
                    'index' => 'indexName',
                    'type' => 'product',
                    'body' => [
                        'product' => [
                            '_all' => [
                                'enabled' => true,
                                'type' => 'text',
                            ],
                            'properties' => [
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
                                            'type' => 'float',
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
                                        ],
                                    ],
                                ]
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
     * Test deleteMapping() method
     */
    public function testDeleteMapping()
    {
        $this->indicesMock->expects($this->once())
            ->method('deleteMapping')
            ->with(
                [
                    'index' => 'indexName',
                    'type' => 'product',
                ]
            );
        $this->model->deleteMapping(
            'indexName',
            'product'
        );
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
    public function testBuildConfig(array $options, $expectedResult): void
    {
        $buildConfig = new Elasticsearch($options);
        $config = $this->getPrivateMethod(Elasticsearch::class, 'buildConfig');
        $result = $config->invoke($buildConfig, $options);
        $this->assertEquals($expectedResult, $result['hosts'][0]);
    }

    /**
     * Return private method for elastic search class.
     *
     * @param $className
     * @param $methodName
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    private function getPrivateMethod($className, $methodName)
    {
        $reflector = new \ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Test deleteMapping() method
     */
    public function testDeleteMappingFailure()
    {
        $this->expectException(\Exception::class);

        $this->indicesMock->expects($this->once())
            ->method('deleteMapping')
            ->with(
                [
                    'index' => 'indexName',
                    'type' => 'product',
                ]
            )
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->deleteMapping(
            'indexName',
            'product'
        );
    }

    /**
     * Test query() method
     * @return void
     */
    public function testQuery()
    {
        $query = 'test phrase query';
        $this->elasticsearchClientMock->expects($this->once())
            ->method('search')
            ->with([$query])
            ->willReturn([]);
        $this->assertEquals([], $this->model->query([$query]));
    }

    /**
     * Test suggest() method
     * @return void
     */
    public function testSuggest()
    {
        $query = 'query';
        $this->elasticsearchClientMock->expects($this->once())
            ->method('suggest')
            ->willReturn([]);
        $this->assertEquals([], $this->model->suggest($query));
    }

    /**
     * Get options data provider.
     */
    public function getOptionsDataProvider()
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
                'expected_result' => 'http://localhost:9200'
            ],
            [
                'with_protocol' => [
                    'hostname' => 'https://localhost',
                    'port' => '9200',
                    'timeout' => 15,
                    'index' => 'magento2',
                    'enableAuth' => 0,
                ],
                'expected_result' => 'https://localhost:9200'
            ]
        ];
    }

    /**
     * Get elasticsearch client options
     *
     * @return array
     */
    protected function getOptions()
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
     * @return array
     */
    protected function getEmptyIndexOption()
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
}
