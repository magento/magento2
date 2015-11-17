<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\DocumentDataMapper;
use Magento\Elasticsearch\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;

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
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var ElasticsearchClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
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
                'getCategoryProductIndexData',
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
        $this->config = $this->getMockBuilder('Magento\Elasticsearch\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods([
                'getIndexName',
                'getEntityType',
            ])
            ->getMock();
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->client = $this->getMockBuilder('Magento\Elasticsearch\Model\Client\Elasticsearch')
            ->disableOriginalConstructor()
            ->setMethods([
                'ping',
                'addDocuments',
                'deleteDocumentsFromIndex',
                'deleteDocumentsByIds',
            ])
            ->getMock();
        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->client);

        $this->model = new ElasticsearchAdapter(
            $this->connectionManager,
            $this->resourceIndex,
            $this->attributeContainer,
            $this->documentDataMapper,
            $this->config,
            $this->logger
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
            ->method('getCategoryProductIndexData')
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
            ->method('addDocuments');
        $this->assertSame(
            $this->model,
            $this->model->addDocs([
                '1' => [
                    'name' => 'Product Name',
                ],
            ])
        );
    }

    /**
     * Test addDocs() method
     * @expectedException \Exception
     */
    public function testAddDocsFailure()
    {
        $this->client->expects($this->once())
            ->method('addDocuments')
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->addDocs([
            '1' => [
                'name' => 'Product Name',
            ],
        ]);
    }

    /**
     * Test cleanIndex() method
     */
    public function testCleanIndex()
    {
        $this->config->expects($this->once())
            ->method('getIndexName')
            ->willReturn('indexName');
        $this->config->expects($this->once())
            ->method('getEntityType')
            ->willReturn('product');
        $this->client->expects($this->once())
            ->method('deleteDocumentsFromIndex');
        $this->assertSame(
            $this->model,
            $this->model->cleanIndex()
        );
    }

    /**
     * Test cleanIndex() method
     * @expectedException \Exception
     */
    public function testCleanIndexFailure()
    {
        $this->config->expects($this->once())
            ->method('getIndexName')
            ->willReturn('indexName');
        $this->config->expects($this->once())
            ->method('getEntityType')
            ->willReturn('product');
        $this->client->expects($this->once())
            ->method('deleteDocumentsFromIndex')
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->cleanIndex();
    }

    /**
     * Test deleteDocs() method
     */
    public function testDeleteDocs()
    {
        $this->config->expects($this->once())
            ->method('getIndexName')
            ->willReturn('indexName');
        $this->config->expects($this->once())
            ->method('getEntityType')
            ->willReturn('product');
        $this->client->expects($this->once())
            ->method('deleteDocumentsByIds');
        $this->assertSame(
            $this->model,
            $this->model->deleteDocs([1, ])
        );
    }

    /**
     * Test deleteDocs() method
     * @expectedException \Exception
     */
    public function testDeleteDocsFailure()
    {
        $this->config->expects($this->once())
            ->method('getIndexName')
            ->willReturn('indexName');
        $this->config->expects($this->once())
            ->method('getEntityType')
            ->willReturn('product');
        $this->client->expects($this->once())
            ->method('deleteDocumentsByIds')
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->deleteDocs([1, ]);
    }
}
