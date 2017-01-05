<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;

/**
 * Class ElasticsearchTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ElasticsearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ElasticsearchAdapter
     */
    protected $model;

    /**
     * @var ConnectionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionManager;

    /**
     * @var DataMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentDataMapper;

    /**
     * @var FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMapper;

    /**
     * @var ClientOptionsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientConfig;

    /**
     * @var BuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexBuilder;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var ElasticsearchClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @var IndexNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexNameResolver;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->connectionManager = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ConnectionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();
        $this->documentDataMapper = $this->getMockBuilder(
            \Magento\Elasticsearch\Model\Adapter\DataMapperInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->fieldMapper = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientConfig = $this->getMockBuilder(\Magento\Elasticsearch\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIndexPrefix',
                'getEntityType',
            ])->getMock();
        $this->indexBuilder = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface::class)
            ->disableOriginalConstructor()
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
        $this->fieldMapper->expects($this->any())
            ->method('getAllAttributesTypes')
            ->willReturn([
                'name' => 'string',
            ]);
        $this->clientConfig->expects($this->any())
            ->method('getIndexPrefix')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');
        $this->indexNameResolver = $this->getMockBuilder(
            \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver::class
        )
            ->setMethods([
                'getIndexName',
                'getIndexNamespace',
                'getIndexFromAlias',
                'getIndexNameForAlias',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Elasticsearch::class,
            [
                'connectionManager' => $this->connectionManager,
                'documentDataMapper' => $this->documentDataMapper,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'indexBuilder' => $this->indexBuilder,
                'logger' => $this->logger,
                'indexNameResolver' => $this->indexNameResolver,
                'options' => [],
            ]
        );
    }

    /**
     * Test ping() method
     */
    public function testPing()
    {
        $this->client->expects($this->once())
            ->method('ping')
            ->willReturn(true);
        $this->assertEquals(true, $this->model->ping());
    }

    /**
     * Test ping() method
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testPingFailure()
    {
        $this->client->expects($this->once())
            ->method('ping')
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->ping();
    }

    /**
     * Test prepareDocsPerStore() method
     */
    public function testPrepareDocsPerStoreEmpty()
    {
        $this->assertEquals([], $this->model->prepareDocsPerStore([], 1));
    }

    /**
     * Test prepareDocsPerStore() method
     */
    public function testPrepareDocsPerStore()
    {
        $this->documentDataMapper->expects($this->once())
            ->method('map')
            ->willReturn([
                'name' => 'Product Name',
            ]);
        $this->assertInternalType(
            'array',
            $this->model->prepareDocsPerStore(
                [
                    '1' => [
                        'name' => 'Product Name',
                    ],
                ],
                1
            )
        );
    }

    /**
     * Test addDocs() method
     */
    public function testAddDocs()
    {
        $this->client->expects($this->once())
            ->method('bulkQuery');
        $this->assertSame(
            $this->model,
            $this->model->addDocs(
                [
                    '1' => [
                        'name' => 'Product Name',
                    ],
                ],
                1,
                'product'
            )
        );
    }

    /**
     * Test addDocs() method
     * @expectedException \Exception
     */
    public function testAddDocsFailure()
    {
        $this->client->expects($this->once())
            ->method('bulkQuery')
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->addDocs(
            [
                '1' => [
                    'name' => 'Product Name',
                ],
            ],
            1,
            'product'
        );
    }

    /**
     * Test cleanIndex() method
     */
    public function testCleanIndex()
    {
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->with(1, 'product', [])
            ->willReturn('indexName_product_1_v');

        $this->client->expects($this->once())
            ->method('isEmptyIndex')
            ->with('indexName_product_1_v')
            ->willReturn(false);
        $this->client->expects($this->atLeastOnce())
            ->method('indexExists')
            ->willReturn(true);
        $this->client->expects($this->once())
            ->method('deleteIndex')
            ->with('_product_1_v1');
        $this->assertSame(
            $this->model,
            $this->model->cleanIndex(1, 'product')
        );
    }

    /**
     * Test cleanIndex() method isEmptyIndex is true
     */
    public function testCleanIndexTrue()
    {
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('indexName_product_1_v');

        $this->client->expects($this->once())
            ->method('isEmptyIndex')
            ->with('indexName_product_1_v')
            ->willReturn(true);

        $this->assertSame(
            $this->model,
            $this->model->cleanIndex(1, 'product')
        );
    }

    /**
     * Test deleteDocs() method
     */
    public function testDeleteDocs()
    {
        $this->client->expects($this->once())
            ->method('bulkQuery');
        $this->assertSame(
            $this->model,
            $this->model->deleteDocs(['1' => 1], 1, 'product')
        );
    }

    /**
     * Test deleteDocs() method
     * @expectedException \Exception
     */
    public function testDeleteDocsFailure()
    {
        $this->client->expects($this->once())
            ->method('bulkQuery')
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->deleteDocs(['1' => 1], 1, 'product');
    }

    /**
     * Test updateAlias() method
     */
    public function testUpdateAliasEmpty()
    {
        $model = $this->objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Elasticsearch::class,
            [
                'connectionManager' => $this->connectionManager,
                'documentDataMapper' => $this->documentDataMapper,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'indexBuilder' => $this->indexBuilder,
                'logger' => $this->logger,
                'indexNameResolver' => $this->indexNameResolver,
                'options' => []
            ]
        );

        $this->client->expects($this->never())
            ->method('updateAlias');

        $this->assertEquals($model, $model->updateAlias(1, 'product'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testConnectException()
    {
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
            \Magento\Elasticsearch\Model\Adapter\Elasticsearch::class,
            [
                'connectionManager' => $connectionManager,
                'documentDataMapper' => $this->documentDataMapper,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'indexBuilder' => $this->indexBuilder,
                'logger' => $this->logger,
                'indexNameResolver' => $this->indexNameResolver,
                'options' => []
            ]
        );
    }

    /**
     * Test updateAlias() method
     */
    public function testUpdateAlias()
    {
        $this->client->expects($this->atLeastOnce())
            ->method('updateAlias');
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexFromAlias')
            ->willReturn('_product_1_v1');

        $this->model->cleanIndex(1, 'product');
        $this->assertEquals($this->model, $this->model->updateAlias(1, 'product'));
    }

    /**
     * Test updateAlias() method
     */
    public function testUpdateAliasWithOldIndex()
    {
        $this->model->cleanIndex(1, 'product');

        $this->indexNameResolver->expects($this->any())
            ->method('getIndexFromAlias')
            ->willReturn('_product_1_v2');

        $this->indexNameResolver->expects($this->any())
            ->method('getIndexNameForAlias')
            ->willReturn('_product_1_v2');

        $this->client->expects($this->any())
            ->method('existsAlias')
            ->with('indexName')
            ->willReturn(true);

        $this->client->expects($this->any())
            ->method('getAlias')
            ->with('indexName')
            ->willReturn(['indexName_product_1_v' => 'indexName_product_1_v']);

        $this->assertEquals($this->model, $this->model->updateAlias(1, 'product'));
    }

    /**
     * Test updateAlias() method
     */
    public function testUpdateAliasWithoutOldIndex()
    {
        $this->model->cleanIndex(1, 'product');
        $this->client->expects($this->any())
            ->method('existsAlias')
            ->with('indexName')
            ->willReturn(true);

        $this->client->expects($this->any())
            ->method('getAlias')
            ->with('indexName')
            ->willReturn(['indexName_product_1_v2' => 'indexName_product_1_v2']);

        $this->assertEquals($this->model, $this->model->updateAlias(1, 'product'));
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
