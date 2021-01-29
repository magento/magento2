<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexNameResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IndexNameResolver
     */
    protected $model;

    /**
     * @var ConnectionManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionManager;

    /**
     * @var ClientOptionsInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $clientConfig;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logger;

    /**
     * @var ElasticsearchClient|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $client;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $entityType;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * Setup method
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->connectionManager = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ConnectionManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getConnection',
            ])
            ->getMock();

        $this->clientConfig = $this->getMockBuilder(\Magento\Elasticsearch\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIndexPrefix',
                'getEntityType',
                'getIndexSettings',
            ])
            ->getMock();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $elasticsearchClientMock = $this->getMockBuilder(\Elasticsearch\Client::class)
            ->setMethods([
                'indices',
                'ping',
                'bulk',
                'search',
                'scroll',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $indicesMock = $this->getMockBuilder(\Elasticsearch\Namespaces\IndicesNamespace::class)
            ->setMethods([
                'exists',
                'getSettings',
                'create',
                'putMapping',
                'deleteMapping',
                'existsAlias',
                'updateAliases',
                'stats'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $elasticsearchClientMock->expects($this->any())
            ->method('indices')
            ->willReturn($indicesMock);
        $this->client = $this->getMockBuilder(\Magento\Elasticsearch\Model\Client\Elasticsearch::class)
            ->setConstructorArgs([
                'options' => $this->getClientOptions(),
                'elasticsearchClient' => $elasticsearchClientMock
            ])
            ->getMock();

        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->client);

        $this->clientConfig->expects($this->any())
            ->method('getIndexPrefix')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');
        $this->entityType = 'product';
        $this->storeId = 1;

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver::class,
            [
                'connectionManager' => $this->connectionManager,
                'clientConfig' => $this->clientConfig,
                'logger' => $this->logger,
                'options' => [],
            ]
        );
    }

    /**
     * Test getIndexNameForAlias() method
     */
    public function testGetIndexNameForAlias()
    {
        $this->clientConfig->expects($this->any())
            ->method('getIndexPrefix')
            ->willReturn('indexName');

        $this->assertEquals(
            'indexName_product_1',
            $this->model->getIndexNameForAlias($this->storeId, $this->entityType)
        );
    }

    /**
     * Test getIndexName() method with prepared index
     */
    public function testGetIndexNameWithPreparedIndex()
    {
        $preparedIndex = ['1' => 'product'];

        $this->assertEquals(
            'product',
            $this->model->getIndexName($this->storeId, $this->entityType, $preparedIndex)
        );
    }

    /**
     * Test getIndexName() method without prepared index
     */
    public function testGetIndexNameWithoutPreparedIndexWithIndexName()
    {
        $preparedIndex = [];

        $this->assertEquals(
            'indexName_product_1_v1',
            $this->model->getIndexName($this->storeId, $this->entityType, $preparedIndex)
        );
    }

    /**
     * Test getIndexPattern() method
     */
    public function testGetIndexPattern()
    {
        $this->assertEquals(
            'indexName_product_1_v',
            $this->model->getIndexPattern($this->storeId, $this->entityType)
        );
    }

    /**
     * Test getIndexFromAlias() method
     */
    public function testUpdateAliasWithOldIndex()
    {
        $this->client->expects($this->any())
            ->method('getAlias')
            ->with('indexName_product_1')
            ->willReturn(
                [
                    'indexName_product_1_v2' => [
                        'aliases' => [
                            'indexName_product_1' => [],
                        ],
                    ],
                ]
            );

        $this->client->expects($this->any())
            ->method('existsAlias')
            ->with('indexName_product_1')
            ->willReturn(true);

        $this->assertEquals(
            'indexName_product_1_v2',
            $this->model->getIndexFromAlias($this->storeId, $this->entityType)
        );
    }

    /**
     */
    public function testConnectException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $connectionManager = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ConnectionManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getConnection',
            ])
            ->getMock();

        $connectionManager->expects($this->any())
            ->method('getConnection')
            ->willThrowException(new \Exception('Something went wrong'));

        $this->objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver::class,
            [
                'connectionManager' => $connectionManager,
                'clientConfig' => $this->clientConfig,
                'logger' => $this->logger,
                'options' => []
            ]
        );
    }

    /**
     * Test getIndexName() indexerId 'catalogsearch_fulltext'
     */
    public function testGetIndexNameCatalogSearchFullText()
    {
        $this->assertEquals(
            'product',
            $this->model->getIndexMapping('catalogsearch_fulltext')
        );
    }

    /**
     * Test getIndexName() with any ndex
     */
    public function testGetIndexName()
    {
        $this->assertEquals(
            'else_index_id',
            $this->model->getIndexMapping('else_index_id')
        );
    }

    /**
     * Get elasticsearch client options
     *
     * @return array
     */
    protected function getClientOptions()
    {
        return [
            'hostname' => 'localhost',
            'port' => '9200',
            'timeout' => 15,
            'index' => 'magento2',
            'enableAuth' => 1,
            'username' => 'user',
            'password' => 'my-password',
        ];
    }
}
