<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\DocumentDataMapper;
use Magento\Elasticsearch\Model\Adapter\FieldMapper;
use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ElasticsearchTest
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
     * @var Index|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceIndex;

    /**
     * @var AttributeContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeContainer;

    /**
     * @var DocumentDataMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentDataMapper;

    /**
     * @var FieldMapper|\PHPUnit_Framework_MockObject_MockObject
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
     * Setup
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->connectionManager = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\ConnectionManager')
            ->disableOriginalConstructor()
            ->setMethods([
                'getConnection',
            ])
            ->getMock();
        $this->resourceIndex = $this->getMockBuilder('Magento\Elasticsearch\Model\ResourceModel\Index')
            ->disableOriginalConstructor()
            ->setMethods([
                'getPriceIndexData',
                'getFullCategoryProductIndexData',
                'getFullProductIndexData',
            ])
            ->getMock();
        $this->attributeContainer = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Container\Attribute')
            ->disableOriginalConstructor()
            ->setMethods([
                'getAttribute',
            ])
            ->getMock();
        $this->documentDataMapper = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\DocumentDataMapper')
            ->disableOriginalConstructor()
            ->setMethods([
                'map',
            ])
            ->getMock();
        $this->fieldMapper = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\FieldMapper')
            ->disableOriginalConstructor()
            ->setMethods(['getAllAttributesTypes'])
            ->getMock();
        $this->clientConfig = $this->getMockBuilder('Magento\Elasticsearch\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods([
                'getIndexName',
                'getEntityType',
                'getIndexSettings',
            ])
            ->getMock();
        $this->indexBuilder = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $elasticsearchClientMock = $this->getMockBuilder('\Elasticsearch\Client')
            ->setMethods([
                'indices',
                'ping',
                'bulk',
                'search',
                'scroll',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $indicesMock = $this->getMockBuilder('\Elasticsearch\Namespaces\IndicesNamespace')
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
        $this->client = $this->getMockBuilder('Magento\Elasticsearch\Model\Client\Elasticsearch')
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
            ->method('getIndexName')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');

        $this->model = $this->objectManager->getObject(
            '\Magento\Elasticsearch\Model\Adapter\Elasticsearch',
            [
                'connectionManager' => $this->connectionManager,
                'resourceIndex' => $this->resourceIndex,
                'attributeContainer' => $this->attributeContainer,
                'documentDataMapper' => $this->documentDataMapper,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'indexBuilder' => $this->indexBuilder,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testConnectException()
    {
        $connectionManager = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\ConnectionManager')
            ->disableOriginalConstructor()
            ->setMethods([
                'getConnection',
            ])
            ->getMock();

        $connectionManager->expects($this->any())
            ->method('getConnection')
            ->willThrowException(new \Exception('Something went wrong'));

        $this->objectManager->getObject(
            '\Magento\Elasticsearch\Model\Adapter\Elasticsearch',
            [
                'connectionManager' => $connectionManager,
                'resourceIndex' => $this->resourceIndex,
                'attributeContainer' => $this->attributeContainer,
                'documentDataMapper' => $this->documentDataMapper,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'indexBuilder' => $this->indexBuilder,
                'logger' => $this->logger
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
        $this->attributeContainer->expects($this->once())
            ->method('getAttribute')
            ->willReturn(1);
        $this->resourceIndex->expects($this->once())
            ->method('getPriceIndexData')
            ->willReturn([]);
        $this->resourceIndex->expects($this->once())
            ->method('getFullCategoryProductIndexData')
            ->willReturn([]);
        $this->resourceIndex->expects($this->once())
            ->method('getFullProductIndexData')
            ->willReturn([
                '1' => [
                    'name' => 'Product Name',
                ],
            ]);
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
                1
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
            1
        );
    }

    /**
     * Test cleanIndex() method
     */
    public function testCleanIndex()
    {
        $this->client->expects($this->once())
            ->method('isEmptyIndex')
            ->with('indexName_1_v1')
            ->willReturn(false);
        $this->client->expects($this->atLeastOnce())
            ->method('indexExists')
            ->willReturn(true);
        $this->client->expects($this->once())
            ->method('deleteIndex')
            ->with('indexName_1_v2');
        $this->assertSame(
            $this->model,
            $this->model->cleanIndex(1)
        );
    }

    /**
     * Test cleanIndex() method isEmptyIndex is true
     */
    public function testCleanIndexTrue()
    {
        $this->client->expects($this->once())
            ->method('isEmptyIndex')
            ->with('indexName_1_v1')
            ->willReturn(true);

        $this->assertSame(
            $this->model,
            $this->model->cleanIndex(1)
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
            $this->model->deleteDocs(['1' => 1], 1)
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
        $this->model->deleteDocs(['1' => 1], 1);
    }

    /**
     * Test updateAlias() method
     */
    public function testUpdateAliasEmpty()
    {
        $model = $this->objectManager->getObject(
            '\Magento\Elasticsearch\Model\Adapter\Elasticsearch',
            [
                'connectionManager' => $this->connectionManager,
                'resourceIndex' => $this->resourceIndex,
                'attributeContainer' => $this->attributeContainer,
                'documentDataMapper' => $this->documentDataMapper,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'indexBuilder' => $this->indexBuilder,
                'logger' => $this->logger
            ]
        );

        $this->client->expects($this->never())
            ->method('updateAlias');
        $this->assertEquals($model, $model->updateAlias(1));
    }

    /**
     * Test updateAlias() method
     */
    public function testUpdateAlias()
    {
        $this->client->expects($this->atLeastOnce())
            ->method('updateAlias');
        $this->model->cleanIndex(1);
        $this->assertEquals($this->model, $this->model->updateAlias(1));
    }

    /**
     * Test updateAlias() method
     */
    public function testUpdateAliasWithOldIndex()
    {
        $this->model->cleanIndex(1);
        $this->client->expects($this->any())
            ->method('existsAlias')
            ->with('indexName')
            ->willReturn(true);

        $this->client->expects($this->any())
            ->method('getAlias')
            ->with('indexName')
            ->willReturn(['indexName_1_v'=>'indexName_1_v']);

        $this->assertEquals($this->model, $this->model->updateAlias(1));
    }

    /**
     * Test updateAlias() method
     */
    public function testUpdateAliasWithoutOldIndex()
    {
        $this->model->cleanIndex(1);
        $this->client->expects($this->any())
            ->method('existsAlias')
            ->with('indexName')
            ->willReturn(true);

        $this->client->expects($this->any())
            ->method('getAlias')
            ->with('indexName')
            ->willReturn(['indexName_1_v2'=>'indexName_1_v2']);

        $this->assertEquals($this->model, $this->model->updateAlias(1));
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
            'password' => 'passwd',
        ];
    }
}
