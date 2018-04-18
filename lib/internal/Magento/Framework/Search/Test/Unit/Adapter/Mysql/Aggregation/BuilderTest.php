<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Aggregation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMetadata;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Search\Request\BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucket;

    /**
     * @var DataProviderContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderContainer;

    /**
     * @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProvider;

    /**
     * @var Builder\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregationContainer;

    /**
     * @var Builder\BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucketBuilder;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var AggregationResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregationResolver;

    /**
     * @var Table|\PHPUnit_Framework_MockObject_MockObject
     */
    private $table;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder
     */
    private $builder;

    /**
     * SetUP method
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->entityMetadata = $this->getMockBuilder('Magento\Framework\Search\EntityMetadata')
            ->setMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->setMethods(['getAggregation'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->bucket = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucketBuilder = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\BucketInterface'
        )
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->aggregationContainer = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container'
        )
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationContainer->expects($this->any())->method('get')->willReturn($this->bucketBuilder);

        $this->connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['fetchAssoc'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProvider = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface'
        )
            ->setMethods(['getDataSet'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProviderContainer = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer'
        )
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataProviderContainer->expects($this->any())->method('get')->willReturn($this->dataProvider);

        $this->resource = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $this->aggregationResolver = $this->getMock(AggregationResolverInterface::class);
        $this->table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder',
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

        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
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
