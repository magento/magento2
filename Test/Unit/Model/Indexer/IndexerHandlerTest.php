<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler;

class IndexerHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexerHandler
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Elasticsearch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $batch;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\ElasticsearchFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterFactory;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->adapter = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Elasticsearch')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapterFactory = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\ElasticsearchFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->adapterFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->adapter);

        $this->batch = $this->getMockBuilder('Magento\Framework\Indexer\SaveHandler\Batch')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            'Magento\Elasticsearch\Model\Indexer\IndexerHandler',
            [
                'adapterFactory' => $this->adapterFactory,
                'batch' => $this->batch,
                'data' => ['indexer_id' => 'catalogsearch_fulltext'],
            ]
        );
    }

    public function testIsAvailable()
    {
        $this->adapter->expects($this->once())
            ->method('ping')
            ->willReturn(true);

        $result = $this->model->isAvailable();

        $this->assertTrue($result);
    }


    public function testDeleteIndex()
    {
        $dimensionValue = 3;
        $documentId = 123;

        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn($dimensionValue);

        $result = $this->model->deleteIndex([$dimension], new \ArrayIterator([$documentId]));

        $this->assertEquals($this->model, $result);
    }

    public function testSaveIndex()
    {
        $dimensionValue = 3;
        $documentId = 123;
        $documents = new \ArrayIterator([$documentId]);

        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
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

        $result = $this->model->saveIndex([$dimension], $documents);

        $this->assertEquals($this->model, $result);
    }

    /**
     * Test cleanIndex() method.
     */
    public function testCleanIndexCatalogSearchFullText()
    {
        $dimensionValue = 'SomeDimension';

        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($dimensionValue);

        $this->adapter->expects($this->once())
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
            'Magento\Elasticsearch\Model\Indexer\IndexerHandler',
            [
                'adapterFactory' => $this->adapterFactory,
                'batch' => $this->batch,
                'data' => ['indexer_id' => 'else_indexer_id'],
            ]
        );
        $dimensionValue = 'SomeDimension';

        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($dimensionValue);

        $this->adapter->expects($this->once())
            ->method('cleanIndex');

        $result = $model->cleanIndex([$dimension]);

        $this->assertEquals($model, $result);
    }
}
