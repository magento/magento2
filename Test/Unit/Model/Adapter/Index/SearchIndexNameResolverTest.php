<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\SearchIndexNameResolver;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SearchIndexNameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchIndexNameResolver
     */
    protected $model;

    /**
     * @var ConnectionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionManager;

    /**
     * @var ClientOptionsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientConfig;

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
    public function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->connectionManager = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\ConnectionManager')
            ->disableOriginalConstructor()
            ->setMethods([
                'getConnection',
            ])
            ->getMock();

        $this->clientConfig = $this->getMockBuilder('Magento\Elasticsearch\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods([
                'getIndexPrefix',
                'getEntityType',
                'getIndexSettings',
            ])
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

        $this->clientConfig->expects($this->any())
            ->method('getIndexPrefix')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');
        $this->entityType = 'catalogsearch_fulltext';
        $this->storeId = 1;

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            '\Magento\Elasticsearch\Model\Adapter\Index\SearchIndexNameResolver',
            [
                'connectionManager' => $this->connectionManager,
                'clientConfig' => $this->clientConfig,
                'logger' => $this->logger,
                'options' => [],
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
            '\Magento\Elasticsearch\Model\Adapter\Index\SearchIndexNameResolver',
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
            'indexName_product_1',
            $this->model->getIndexName($this->storeId, $this->entityType)
        );
    }

    /**
     * Test getIndexName() with any ndex
     */
    public function testGetIndexName()
    {
        $this->assertEquals(
            'indexName_else_index_id_1',
            $this->model->getIndexName($this->storeId, 'else_index_id')
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
