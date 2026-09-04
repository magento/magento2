<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\ResourceModel\Query;
use Magento\Search\Model\Query as SearchQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    /**
     * @var Query
     */
    private $model;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapter;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->adapter = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getResources')
            ->willReturn($resource);

        $this->model = $objectManager->getObject(
            Query::class,
            ['context' => $context]
        );
    }

    public function testSaveIncrementalPopularity()
    {
        /** @var SearchQuery|MockObject $model */
        $model = $this->getMockBuilder(SearchQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $model->expects($this->any())
            ->method('getQueryText')
            ->willReturn('queryText');

        $this->adapter->expects($this->once())
            ->method('insertOnDuplicate');

        $this->model->saveIncrementalPopularity($model);
    }

    public function testSaveNumResults()
    {
        /** @var SearchQuery|MockObject $model */
        $model = $this->createMock(SearchQuery::class);
        $model->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $model->expects($this->any())
            ->method('getQueryText')
            ->willReturn('queryText');
        $model->setData('num_results', 30);

        $this->adapter->expects($this->once())
            ->method('insertOnDuplicate');

        $this->model->saveNumResults($model);
    }
}
