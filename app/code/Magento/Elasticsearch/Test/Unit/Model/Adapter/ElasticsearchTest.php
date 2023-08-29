<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Exception;
use Magento\AdvancedSearch\Model\Client\ClientInterface as ElasticsearchClient;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Elasticsearch7\Model\Client\Elasticsearch;
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
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
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
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        if (!class_exists(\Elasticsearch\ClientBuilder::class)) { /** @phpstan-ignore-line */
            $this->markTestSkipped('AC-6597: Skipped as Elasticsearch 8 is configured');
        }

        $this->objectManager = new ObjectManagerHelper($this);
        $this->connectionManager = $this->getMockBuilder(ConnectionManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection'])
            ->getMock();
        $this->fieldMapper = $this->getMockBuilder(FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->clientConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIndexPrefix', 'getEntityType'])->getMock();
        $this->indexBuilder = $this->getMockBuilder(BuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $elasticsearchClientMock = $this->getMockBuilder(Client::class)
            ->onlyMethods(['indices', 'ping', 'bulk', 'search'])
            ->disableOriginalConstructor()
            ->getMock();
        $indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->onlyMethods(
                [
                    'exists',
                    'getSettings',
                    'create',
                    'putMapping',
                    'existsAlias',
                    'updateAliases',
                    'stats'
                ]
            )
            ->addMethods(['deleteMapping'])
            ->disableOriginalConstructor()
            ->getMock();
        $elasticsearchClientMock->expects($this->any())
            ->method('indices')
            ->willReturn($indicesMock);
        $this->client = $this->getMockBuilder(Elasticsearch::class)
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
            ->with(
                [
                    'entityType' => 'product',
                    'websiteId' => 1,
                    'storeId' => 1,
                ]
            )
            ->willReturn(
                [
                    'name' => [
                        'type' => 'string',
                        'fields' => [
                            'keyword' => [
                                'type' => "keyword"
                            ]
                        ]
                    ]
                ]
            );
        $this->clientConfig->expects($this->any())
            ->method('getIndexPrefix')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');
        $this->indexNameResolver = $this->getMockBuilder(IndexNameResolver::class)
            ->onlyMethods(
                [
                    'getIndexName',
                    'getIndexNamespace',
                    'getIndexFromAlias',
                    'getIndexNameForAlias'
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
            ElasticsearchAdapter::class,
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
                'arrayManager' => $this->arrayManager
            ]
        );
    }

    /**
     * Test ping() method
     *
     * @return void
     */
    public function testPing(): void
    {
        $this->client->expects($this->once())
            ->method('ping')
            ->willReturn(true);
        $this->assertTrue($this->model->ping());
    }

    /**
     * Test ping() method
     *
     * @return void
     */
    public function testPingFailure(): void
    {
        $this->expectException(LocalizedException::class);

        $this->client->expects($this->once())
            ->method('ping')
            ->willThrowException(new Exception('Something went wrong'));
        $this->model->ping();
    }

    /**
     * Test prepareDocsPerStore() method
     *
     * @return void
     */
    public function testPrepareDocsPerStoreEmpty(): void
    {
        $this->assertEquals([], $this->model->prepareDocsPerStore([], 1));
    }

    /**
     * Test prepareDocsPerStore() method
     *
     * @return void
     */
    public function testPrepareDocsPerStore(): void
    {
        $this->batchDocumentDataMapper->expects($this->once())
            ->method('map')
            ->willReturn(
                [
                    'name' => 'Product Name',
                ]
            );
        $this->assertIsArray($this->model->prepareDocsPerStore(
            ['1' => ['name' => 'Product Name'],
            ],
            1
        ));
    }

    /**
     * Test addDocs() method
     *
     * @return void
     */
    public function testAddDocs(): void
    {
        $this->client->expects($this->once())
            ->method('bulkQuery');
        $this->assertSame(
            $this->model,
            $this->model->addDocs(
                ['1' => ['name' => 'Product Name'],
                ],
                1,
                'product'
            )
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAddDocsStackedQueries(): void
    {
        $this->client->expects($this->once())
            ->method('bulkQuery');
        $this->model->enableStackQueriesMode();
        $this->assertSame(
            $this->model,
            $this->model->addDocs(
                ['1' => ['name' => 'Product Name'],
                ],
                1,
                'product'
            )
        );
        $this->model->triggerStackedQueries();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testTriggerStackedQueriesWhenEmpty(): void
    {
        $this->client->expects($this->never())
            ->method('bulkQuery');
        $this->model->enableStackQueriesMode();
        $this->model->triggerStackedQueries();
    }

    /**
     * Test addDocs() method
     *
     * @return void
     */
    public function testAddDocsFailure(): void
    {
        $this->expectException(Exception::class);

        $this->client->expects($this->once())
            ->method('bulkQuery')
            ->willThrowException(new Exception('Something went wrong'));
        $this->model->addDocs(
            ['1' => ['name' => 'Product Name']],
            1,
            'product'
        );
    }

    /**
     * Test cleanIndex() method
     *
     * @return void
     */
    public function testCleanIndex(): void
    {
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->with(1, 'product', [])
            ->willReturn('_product_1_v1');
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexNameForAlias')
            ->with(1, 'product')
            ->willReturn('_product_1');

        $this->client->expects($this->atLeastOnce())
            ->method('indexExists')
            ->willReturnMap(
                [
                    ['_product_1_v1', true],
                    ['_product_1_v2', true],
                    ['_product_1_v3', false],
                ]
            );
        $this->client->expects($this->exactly(1))
            ->method('deleteIndex')
            ->willReturnMap([
                ['_product_1_v1'],
                ['_product_1_v2'],
            ]);
        $this->assertSame(
            $this->model,
            $this->model->cleanIndex(1, 'product')
        );
    }

    /**
     * Test deleteDocs() method
     *
     * @return void
     */
    public function testDeleteDocs(): void
    {
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('_product_1_v1');
        $this->client->expects($this->once())
            ->method('bulkQuery');
        $this->assertSame(
            $this->model,
            $this->model->deleteDocs(['1' => 1], 1, 'product')
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDeleteDocsStackedQueries(): void
    {
        $this->client->expects($this->once())
            ->method('bulkQuery');
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('_product_1_v1');
        $this->assertSame(
            $this->model,
            $this->model->deleteDocs(['1' => 1], 1, 'product')
        );
        $this->model->enableStackQueriesMode();
        $this->model->triggerStackedQueries();
    }

    /**
     * Test deleteDocs() method
     *
     * @return void
     */
    public function testDeleteDocsFailure(): void
    {
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('_product_1_v1');

        $this->expectException(Exception::class);

        $this->client->expects($this->once())
            ->method('bulkQuery')
            ->willThrowException(new Exception('Something went wrong'));
        $this->model->deleteDocs(['1' => 1], 1, 'product');
    }

    /**
     * Test updateAlias() method
     *
     * @return void
     */
    public function testUpdateAliasEmpty(): void
    {
        $model = $this->objectManager->getObject(
            ElasticsearchAdapter::class,
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

    /**
     * @return void
     */
    public function testConnectException(): void
    {
        $this->expectException(LocalizedException::class);

        $connectionManager = $this->getMockBuilder(ConnectionManager::class)->disableOriginalConstructor()
            ->onlyMethods(['getConnection'])
            ->getMock();

        $connectionManager->expects($this->any())
            ->method('getConnection')
            ->willThrowException(new Exception('Something went wrong'));

        $this->objectManager->getObject(
            ElasticsearchAdapter::class,
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
     *
     * @return void
     */
    public function testUpdateAlias(): void
    {
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('_product_1_v1');

        $this->indexNameResolver->expects($this->any())
            ->method('getIndexNameForAlias')
            ->with(1, 'product')
            ->willReturn('_product_1');
        $this->client->expects($this->atLeastOnce())
            ->method('updateAlias');
        $this->indexNameResolver
            ->method('getIndexFromAlias')
            ->willReturn('_product_1_v1');

        $this->emulateCleanIndex();
        $this->assertEquals($this->model, $this->model->updateAlias(1, 'product'));
    }

    /**
     * Test updateAlias() method
     *
     * @return void
     */
    public function testUpdateAliasWithOldIndex(): void
    {
        $this->emulateCleanIndex();

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
     *
     * @return void
     */
    public function testUpdateAliasWithoutOldIndex(): void
    {
        $this->emulateCleanIndex();
        $this->client->expects($this->any())
            ->method('existsAlias')
            ->with('indexName')
            ->willReturn(true);

        $this->client->expects($this->any())
            ->method('getAlias')
            ->with('indexName')
            ->willReturn(['indexName_product_1_v2' => 'indexName_product_1_v2']);

        $this->indexNameResolver->expects($this->any())
            ->method('getIndexFromAlias')
            ->with(1, 'product')
            ->willReturn('_product_1');

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
     * Test for get mapping total fields limit
     *
     * @return void
     */
    public function testGetMappingTotalFieldsLimit(): void
    {
        $settings = [
            'index' => [
                    'mapping' => [
                        'total_fields' => [
                            'limit'  => 1002
                        ]
                    ]
            ]
        ];
        $this->client
            ->method('createIndex')
            ->withConsecutive([null, ['settings' => $settings]]);
        $this->emulateCleanIndex();
    }

    /**
     * Get elasticsearch client options
     *
     * @return array
     */
    protected function getClientOptions(): array
    {
        return [
            'hostname' => 'localhost',
            'port' => '9200',
            'timeout' => 15,
            'index' => 'magento2',
            'enableAuth' => 1,
            'username' => 'user',
            'password' => 'my-password'
        ];
    }

    /**
     * Run Clean Index; Index Name Mock value should be non-nullable for PHP 8.1 compatibility
     *
     * @return void
     */
    private function emulateCleanIndex(): void
    {
        $this->indexNameResolver
            ->method('getIndexName')
            ->willReturn('');
        $this->indexNameResolver->expects($this->any())
            ->method('getIndexNameForAlias')
            ->with(1, 'product')
            ->willReturn('_product_1');
        $this->model->cleanIndex(1, 'product');
    }
}
