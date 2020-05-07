<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Aggregation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends TestCase
{
    /**
     * @var EntityMetadata|MockObject
     */
    private $entityMetadata;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var BucketInterface|MockObject
     */
    private $bucket;

    /**
     * @var DataProviderContainer|MockObject
     */
    private $dataProviderContainer;

    /**
     * @var DataProviderInterface|MockObject
     */
    private $dataProvider;

    /**
     * @var Builder\Container|MockObject
     */
    private $aggregationContainer;

    /**
     * @var Builder\BucketInterface|MockObject
     */
    private $bucketBuilder;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var AggregationResolverInterface|MockObject
     */
    private $aggregationResolver;

    /**
     * @var Table|MockObject
     */
    private $table;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder
     */
    private $builder;

    /**
     * SetUP method
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->entityMetadata = $this->getMockBuilder(EntityMetadata::class)
            ->setMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getAggregation'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->bucket = $this->getMockBuilder(BucketInterface::class)
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucketBuilder = $this->getMockBuilder(
            \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\BucketInterface::class
        )
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->aggregationContainer = $this->getMockBuilder(
            Container::class
        )
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationContainer->expects($this->any())->method('get')->willReturn($this->bucketBuilder);

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['fetchAssoc'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProvider = $this->getMockBuilder(
            DataProviderInterface::class
        )
            ->setMethods(['getDataSet'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProviderContainer = $this->getMockBuilder(
            DataProviderContainer::class
        )
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataProviderContainer->expects($this->any())->method('get')->willReturn($this->dataProvider);

        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $this->aggregationResolver = $this->getMockForAbstractClass(AggregationResolverInterface::class);
        $this->table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = $helper->getObject(
            Builder::class,
            [
                'entityMetadata' => $this->entityMetadata,
                'dataProviderContainer' => $this->dataProviderContainer,
                'resource' => $this->resource,
                'aggregationContainer' => $this->aggregationContainer,
                'aggregationResolver' => $this->aggregationResolver,
            ]
        );
    }

    /**
     * Test for method "build"
     */
    public function testBuild()
    {
        $fetchResult = ['name' => ['some', 'result']];
        $documents = [1 => 'document_1', 2 => 'document_2'];

        $this->aggregationResolver->expects($this->once())
            ->method('resolve')
            ->with($this->request, array_keys($documents))
            ->willReturn([$this->bucket]);
        $this->bucket->expects($this->once())->method('getName')->willReturn('name');
        $this->request->expects($this->once())->method('getDimensions')->willReturn([]);
        $this->bucketBuilder->expects($this->once())->method('build')->willReturn($fetchResult['name']);

        $result = $this->builder->build($this->request, $this->table, $documents);

        $this->assertEquals($fetchResult, $result);
    }

    public function testBuildWithoutPassedDocuments()
    {
        $documentIds = [1, 2];
        $tableName = 'table_name';

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())
            ->method('from')
            ->with($tableName, TemporaryStorage::FIELD_ENTITY_ID)
            ->willReturnSelf();

        $this->table->expects($this->once())->method('getName')->willReturn($tableName);
        $this->connectionMock
            ->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $this->connectionMock
            ->expects($this->once())
            ->method('fetchCol')
            ->willReturn($documentIds);

        $this->aggregationResolver->expects($this->once())
            ->method('resolve')
            ->with($this->request, $documentIds)
            ->willReturn([]);

        $this->builder->build($this->request, $this->table);
    }
}
