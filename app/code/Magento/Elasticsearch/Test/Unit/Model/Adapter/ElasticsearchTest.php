<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Magento\AdvancedSearch\Model\Client\ClientInterface as ElasticsearchClient;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField;
use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for Elasticsearch client
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ElasticsearchTest extends TestCase
{
    /**
     * @var ElasticsearchAdapter
     */
    protected $model;

    /**
     * @var ConnectionManager|MockObject
     */
    protected $connectionManager;

    /**
     * @var BatchDataMapperInterface|MockObject
     */
    protected $batchDocumentDataMapper;

    /**
     * @var FieldMapperInterface|MockObject
     */
    protected $fieldMapper;

    /**
     * @var ClientOptionsInterface|MockObject
     */
    protected $clientConfig;

    /**
     * @var BuilderInterface|MockObject
     */
    protected $indexBuilder;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var ElasticsearchClient|MockObject
     */
    protected $client;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @var IndexNameResolver|MockObject
     */
    protected $indexNameResolver;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $productAttributeRepository;

    /**
     * @var StaticField|MockObject
     */
    private $staticFieldProvider;

    /**
     * @var ArrayManager|MockObject
     */
    private $arrayManager;

    /**
     * Setup
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->connectionManager = $this->getMockBuilder(ConnectionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();
        $this->fieldMapper = $this->getMockBuilder(FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->clientConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getIndexPrefix',
                    'getEntityType',
                ]
            )->getMock();
        $this->indexBuilder = $this->getMockBuilder(BuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $elasticsearchClientMock = $this->getMockBuilder(Client::class)
            ->setMethods(
                [
                    'indices',
                    'ping',
                    'bulk',
                    'search',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->setMethods(
                [
                    'exists',
                    'getSettings',
                    'create',
                    'putMapping',
                    'deleteMapping',
                    'existsAlias',
                    'updateAliases',
                    'stats'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $elasticsearchClientMock->expects($this->any())
            ->method('indices')
            ->willReturn($indicesMock);
        $this->client = $this->getMockBuilder(\Magento\Elasticsearch\Elasticsearch5\Model\Client\Elasticsearch::class)
            ->setConstructorArgs(
                [
                    'options' => $this->getClientOptions(),
                    'elasticsearchClient' => $elasticsearchClientMock
                ]
            )
            ->getMock();
        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->client);
        $this->fieldMapper->expects($this->any())
            ->method('getAllAttributesTypes')
            ->willReturn(
                [
                    'name' => 'string',
                ]
            );
        $this->clientConfig->expects($this->any())
            ->method('getIndexPrefix')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');
        $this->indexNameResolver = $this->getMockBuilder(
            IndexNameResolver::class
        )
            ->setMethods(
                [
                    'getIndexName',
                    'getIndexNamespace',
                    'getIndexFromAlias',
                    'getIndexNameForAlias',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->batchDocumentDataMapper = $this->getMockBuilder(BatchDataMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productAttributeRepository = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->staticFieldProvider = $this->getMockBuilder(StaticField::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayManager = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Elasticsearch::class,
            [
                'connectionManager' => $this->connectionManager,
                'batchDocumentDataMapper' => $this->batchDocumentDataMapper,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'indexBuilder' => $this->indexBuilder,
                'logger' => $this->logger,
                'indexNameResolver' => $this->indexNameResolver,
                'options' => [],
                'productAttributeRepository' => $this->productAttributeRepository,
                'staticFieldProvider' => $this->staticFieldProvider,
                'arrayManager' => $this->arrayManager,
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
        $this->assertTrue($this->model->ping());
    }

    /**
     * Test ping() method
     */
    public function testPingFailure()
    {
        $this->expectException(LocalizedException::class);

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
        $this->batchDocumentDataMapper->expects($this->once())
            ->method('map')
            ->willReturn(
                [
                    'name' => 'Product Name',
                ]
            );
        $this->assertIsArray($this->model->prepareDocsPerStore(
            [
                '1' => [
                    'name' => 'Product Name',
                ],
            ],
            1
        ));
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
     */
    public function testAddDocsFailure()
    {
        $this->expectException(\Exception::class);

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
     */
    public function testDeleteDocsFailure()
    {
        $this->expectException(\Exception::class);

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
                'batchDocumentDataMapper' => $this->batchDocumentDataMapper,
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

    public function testConnectException()
    {
        $this->expectException(LocalizedException::class);

        $connectionManager = $this->getMockBuilder(ConnectionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getConnection',
                ]
            )
            ->getMock();

        $connectionManager->expects($this->any())
            ->method('getConnection')
            ->willThrowException(new \Exception('Something went wrong'));

        $this->objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Elasticsearch::class,
            [
                'connectionManager' => $connectionManager,
                'batchDocumentDataMapper' => $this->batchDocumentDataMapper,
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
     * Test update Elasticsearch mapping for index without alias definition.
     *
     * @return void
     */
    public function testUpdateIndexMappingWithoutAliasDefinition(): void
    {
        $storeId = 1;
        $mappedIndexerId = 'product';

        $this->indexNameResolver->expects($this->once())
            ->method('getIndexFromAlias')
            ->with($storeId, $mappedIndexerId)
            ->willReturn('');

        $this->productAttributeRepository->expects($this->never())
            ->method('get');

        $this->model->updateIndexMapping($storeId, $mappedIndexerId, 'attribute_code');
    }

    /**
     * Test update Elasticsearch mapping for index with alias definition.
     *
     * @return void
     */
    public function testUpdateIndexMappingWithAliasDefinition(): void
    {
        $storeId = 1;
        $mappedIndexerId = 'product';
        $indexName = '_product_1_v1';
        $attributeCode = 'example_attribute_code';

        $this->indexNameResolver->expects($this->once())
            ->method('getIndexFromAlias')
            ->with($storeId, $mappedIndexerId)
            ->willReturn($indexName);

        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productAttributeRepository->expects($this->once())
            ->method('get')
            ->with($attributeCode)
            ->willReturn($attribute);

        $this->staticFieldProvider->expects($this->once())
            ->method('getField')
            ->with($attribute)
            ->willReturn([$attributeCode => ['type' => 'text']]);

        $mappedAttributes = ['another_attribute_code' => 'attribute_mapping'];
        $this->client->expects($this->once())
            ->method('getMapping')
            ->with(['index' => $indexName])
            ->willReturn(['properties' => $mappedAttributes]);

        $this->arrayManager->expects($this->once())
            ->method('findPath')
            ->with('properties', ['properties' => $mappedAttributes])
            ->willReturn('example/path/to/properties');

        $this->arrayManager->expects($this->once())
            ->method('get')
            ->with('example/path/to/properties', ['properties' => $mappedAttributes], [])
            ->willReturn($mappedAttributes);

        $this->client->expects($this->once())
            ->method('addFieldsMapping')
            ->with([$attributeCode => ['type' => 'text']], $indexName, 'product');

        $this->model->updateIndexMapping($storeId, $mappedIndexerId, $attributeCode);
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
