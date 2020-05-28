<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Indexer;

use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Elasticsearch\Model\Indexer\IndexerHandler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerHandlerTest extends TestCase
{
    /**
     * @var IndexerHandler
     */
    private $model;

    /**
     * @var Elasticsearch|MockObject
     */
    private $adapter;

    /**
     * @var Batch|MockObject
     */
    private $batch;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\ElasticsearchFactory|MockObject
     */
    private $adapterFactory;

    /**
     * @var IndexStructureInterface|MockObject
     */
    private $indexStructure;

    /**
     * @var IndexNameResolver|MockObject
     */
    private $indexNameResolver;

    /**
     * @var ClientInterface|MockObject
     */
    private $client;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolver;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scopeInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->adapter = $this->getMockBuilder(Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapterFactory = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\ElasticsearchFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->adapterFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->adapter);

        $this->batch = $this->getMockBuilder(Batch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexStructure = $this->getMockBuilder(IndexStructureInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->indexNameResolver = $this->getMockBuilder(
            IndexNameResolver::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->client = $this->getMockBuilder(ClientInterface::class)
            ->setMethods(['ping', 'testConnection','prepareDocsPerStore','addDocs', 'cleanIndex'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scopeResolver = $this->getMockForAbstractClass(
            ScopeResolverInterface::class,
            [],
            '',
            false
        );

        $this->scopeInterface = $this->getMockForAbstractClass(
            ScopeInterface::class,
            [],
            '',
            false
        );

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            IndexerHandler::class,
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

        $dimension = $this->getMockBuilder(Dimension::class)
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

        $dimension = $this->getMockBuilder(Dimension::class)
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

        $dimension = $this->getMockBuilder(Dimension::class)
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
            IndexerHandler::class,
            [
                'adapterFactory' => $this->adapterFactory,
                'batch' => $this->batch,
                'data' => ['indexer_id' => 'else_indexer_id'],
            ]
        );
        $dimensionValue = 'SomeDimension';

        $dimension = $this->getMockBuilder(Dimension::class)
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
