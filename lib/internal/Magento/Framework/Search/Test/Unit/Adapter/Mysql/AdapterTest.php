<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Adapter;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;
use Magento\Framework\Search\Adapter\Mysql\Mapper;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Mysql search adapter test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdapterTest extends TestCase
{
    /**
     * @var ResponseFactory|MockObject
     */
    protected $responseFactory;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionAdapter;

    /**
     * @var Mapper|MockObject
     */
    private $mapper;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var BucketInterface|MockObject
     */
    private $bucket;

    /**
     * @var Builder|MockObject
     */
    private $aggregatioBuilder;

    /**
     * @var TemporaryStorage|MockObject
     */
    private $temporaryStorage;

    protected function setUp(): void
    {
        $this->markTestSkipped("MC-18948: Mysql Adapter and Search Engine is deprecated");
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getAggregation'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->select = $this->getMockBuilder(Select::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['fetchAssoc'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionAdapter);

        $this->mapper = $this->getMockBuilder(Mapper::class)
            ->setMethods(['buildQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseFactory = $this->getMockBuilder(ResponseFactory::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aggregatioBuilder = $this->getMockBuilder(
            Builder::class
        )->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = $this->getMockBuilder(BucketInterface::class)
            ->setMethods(['getType', 'getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->temporaryStorage = $this->getMockBuilder(TemporaryStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $temporaryStorageFactoryName = TemporaryStorageFactory::class;
        $temporaryStorageFactory = $this->getMockBuilder($temporaryStorageFactoryName)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $temporaryStorageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->temporaryStorage);

        $this->adapter = $this->objectManager->getObject(
            Adapter::class,
            [
                'mapper' => $this->mapper,
                'responseFactory' => $this->responseFactory,
                'resource' => $this->resource,
                'aggregationBuilder' => $this->aggregatioBuilder,
                'temporaryStorageFactory' => $temporaryStorageFactory,
            ]
        );
    }

    public function testQuery()
    {
        $selectResult = [
            'documents' => [
                [
                    'product_id' => 1,
                    'sku' => 'Product',
                ],
            ],
            'aggregations' => [
                'aggregation_name' => [
                    'aggregation1' => [1, 3],
                    'aggregation2' => [2, 4],
                ],
            ],
            'total' => 1
        ];

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionAdapter->expects($this->exactly(2))
            ->method('select')
            ->willReturn($select);

        $this->connectionAdapter->expects($this->once())
            ->method('fetchOne')
            ->with($select)
            ->willReturn($selectResult['total']);

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->temporaryStorage->expects($this->any())
            ->method('storeDocumentsFromSelect')
            ->willReturn($table);

        $this->connectionAdapter->expects($this->any())
            ->method('fetchAssoc')
            ->willReturn($selectResult['documents']);
        $this->mapper->expects($this->once())
            ->method('buildQuery')
            ->with($this->request)
            ->willReturn($this->select);
        $this->responseFactory->expects($this->once())
            ->method('create')
            ->with($selectResult)
            ->willReturnArgument(0);
        $this->aggregatioBuilder->expects($this->once())
            ->method('build')
            ->with($this->request, $table, $selectResult['documents'])
            ->willReturn($selectResult['aggregations']);
        $response = $this->adapter->query($this->request);
        $this->assertEquals($selectResult, $response);
    }
}
