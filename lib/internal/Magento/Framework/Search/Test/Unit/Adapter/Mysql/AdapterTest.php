<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionAdapter;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapper;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Adapter
     */
    private $adapter;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucket;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregatioBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $temporaryStorage;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder(\Magento\Framework\Search\RequestInterface::class)
            ->setMethods(['getAggregation'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionAdapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->setMethods(['fetchAssoc'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionAdapter));

        $this->mapper = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\Mapper::class)
            ->setMethods(['buildQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseFactory = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\ResponseFactory::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aggregatioBuilder = $this->getMockBuilder(
            \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder::class
        )->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->setMethods(['getType', 'getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->temporaryStorage = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\TemporaryStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $temporaryStorageFactoryName = \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory::class;
        $temporaryStorageFactory = $this->getMockBuilder($temporaryStorageFactoryName)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $temporaryStorageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->temporaryStorage);

        $this->adapter = $this->objectManager->getObject(
            \Magento\Framework\Search\Adapter\Mysql\Adapter::class,
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

        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionAdapter->expects($this->exactly(2))
            ->method('select')
            ->willReturn($select);

        $this->connectionAdapter->expects($this->once())
            ->method('fetchOne')
            ->with($select)
            ->willReturn($selectResult['total']);

        $table = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->temporaryStorage->expects($this->any())
            ->method('storeDocumentsFromSelect')
            ->willReturn($table);

        $this->connectionAdapter->expects($this->any())
            ->method('fetchAssoc')
            ->will($this->returnValue($selectResult['documents']));
        $this->mapper->expects($this->once())
            ->method('buildQuery')
            ->with($this->request)
            ->will($this->returnValue($this->select));
        $this->responseFactory->expects($this->once())
            ->method('create')
            ->with($selectResult)
            ->will($this->returnArgument(0));
        $this->aggregatioBuilder->expects($this->once())
            ->method('build')
            ->with($this->request, $table, $selectResult['documents'])
            ->willReturn($selectResult['aggregations']);
        $response = $this->adapter->query($this->request);
        $this->assertEquals($selectResult, $response);
    }
}
