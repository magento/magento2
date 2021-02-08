<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler;

class IndexerHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IndexerHandler
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Elasticsearch|\PHPUnit\Framework\MockObject\MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch|\PHPUnit\Framework\MockObject\MockObject
     */
    private $batch;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\ElasticsearchFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $adapterFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexStructureInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexStructure;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexNameResolver;

    /**
     * @var \Magento\Elasticsearch\Model\Client\Elasticsearch|\PHPUnit\Framework\MockObject\MockObject
     */
    private $client;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeResolver;

    /**
     * @var \Magento\Framework\App\ScopeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->adapter = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapterFactory = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\ElasticsearchFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->adapterFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->adapter);

        $this->batch = $this->getMockBuilder(\Magento\Framework\Indexer\SaveHandler\Batch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexStructure = $this->getMockBuilder(\Magento\Framework\Indexer\IndexStructureInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexNameResolver = $this->getMockBuilder(
            \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->client = $this->getMockBuilder(\Magento\Elasticsearch\Model\Client\Elasticsearch::class)
            ->setMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeResolver = $this->getMockForAbstractClass(
            \Magento\Framework\App\ScopeResolverInterface::class,
            [],
            '',
            false
        );

        $this->scopeInterface = $this->getMockForAbstractClass(
            \Magento\Framework\App\ScopeInterface::class,
            [],
            '',
            false
        );

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Indexer\IndexerHandler::class,
            [
                'indexStructure' => $this->indexStructure,
                'adapter' => $this->adapter,
                'indexNameResolver' => $this->indexNameResolver,
                'batch' => $this->batch,
                'data' => ['indexer_id' => 'catalogsearch_fulltext'],
                500,
                'scopeResolver' => $this->scopeResolver
            ]
        );
    }

    public function testIsAvailable()
    {
        $this->adapter->expects($this->any())
            ->method('ping')
            ->willReturn(true);

        $this->client->expects($this->any())
            ->method('ping')
            ->willReturn(true);

        $result = $this->model->isAvailable();

        $this->assertTrue($result);
    }

    public function testDeleteIndex()
    {
        $dimensionValue = 3;
        $documentId = 123;

        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn($dimensionValue);
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->once())
            ->method('getId')
            ->willReturn($dimensionValue);

        $result = $this->model->deleteIndex([$dimension], new \ArrayIterator([$documentId]));

        $this->assertEquals($this->model, $result);
    }

    public function testSaveIndex()
    {
        $dimensionValue = 3;
        $documentId = 123;
        $documents = new \ArrayIterator([$documentId]);

        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn($dimensionValue);

        $this->batch->expects($this->once())
            ->method('getItems')
            ->with($documents, 500)
            ->willReturn([[]]);

        $this->adapter->expects($this->once())
            ->method('prepareDocsPerStore')
            ->with([], $dimensionValue)
            ->willReturn([$documentId]);
        $this->adapter->expects($this->once())
            ->method('addDocs')
            ->with([$documentId]);
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->once())
            ->method('getId')
            ->willReturn($dimensionValue);

        $result = $this->model->saveIndex([$dimension], $documents);

        $this->assertEquals($this->model, $result);
    }

    /**
     * Test cleanIndex() method.
     */
    public function testCleanIndexCatalogSearchFullText()
    {
        $dimensionValue = 'SomeDimension';

        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($dimensionValue);

        $this->adapter->expects($this->any())
            ->method('cleanIndex');

        $result = $this->model->cleanIndex([$dimension]);

        $this->assertEquals($this->model, $result);
    }

    /**
     * Test cleanIndex() method.
     */
    public function testCleanIndex()
    {
        $objectManager = new ObjectManagerHelper($this);
        $model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Indexer\IndexerHandler::class,
            [
                'adapterFactory' => $this->adapterFactory,
                'batch' => $this->batch,
                'data' => ['indexer_id' => 'else_indexer_id'],
            ]
        );
        $dimensionValue = 'SomeDimension';

        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($dimensionValue);

        $this->adapter->expects($this->any())
            ->method('cleanIndex');

        $result = $model->cleanIndex([$dimension]);

        $this->assertEquals($model, $result);
    }
}
