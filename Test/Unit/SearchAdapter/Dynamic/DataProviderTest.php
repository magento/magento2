<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Dynamic;

use Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var ConnectionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionManager;

    /**
     * @var FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMapper;

    /**
     * @var Range|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $range;

    /**
     * @var IntervalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $intervalFactory;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientConfig;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var EntityStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityStorage;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var ElasticsearchClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var SearchIndexNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchIndexNameResolver;

    /**
     * Setup method
     * @return void
     */
    protected function setUp()
    {
        $this->connectionManager = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\ConnectionManager')
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->range = $this->getMockBuilder('Magento\Catalog\Model\Layer\Filter\Price\Range')
            ->setMethods(['getPriceRange'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->intervalFactory = $this->getMockBuilder('Magento\Framework\Search\Dynamic\IntervalFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientConfig = $this->getMockBuilder('Magento\Elasticsearch\Model\Config')
            ->setMethods([
                'getIndexName',
                'getEntityType',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->setMethods(['getCustomerGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityStorage = $this->getMockBuilder('Magento\Framework\Search\Dynamic\EntityStorage')
            ->setMethods(['getSource'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityStorage->expects($this->any())
            ->method('getSource')
            ->willReturn([1]);
        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->storeMock = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->clientConfig->expects($this->any())
            ->method('getIndexName')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');
        $this->clientMock = $this->getMockBuilder('Magento\Elasticsearch\Model\Client\Elasticsearch')
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->clientMock);

        $this->fieldMapper = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\FieldMapperInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchIndexNameResolver = $this
            ->getMockBuilder('Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            '\Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider',
            [
                'connectionManager' => $this->connectionManager,
                'fieldMapper' => $this->fieldMapper,
                'range' => $this->range,
                'intervalFactory' => $this->intervalFactory,
                'clientConfig' => $this->clientConfig,
                'storeManager' => $this->storeManager,
                'customerSession' => $this->customerSession,
                'searchIndexNameResolver' => $this->searchIndexNameResolver,
                'indexerId' => 'catalogsearch_fulltext'
            ]
        );
    }

    /**
     * Test getRange() method
     */
    public function testGetRange()
    {
        $this->range->expects($this->once())
            ->method('getPriceRange')
            ->willReturn([]);
        $this->assertEquals(
            [],
            $this->model->getRange()
        );
    }

    /**
     * Test getAggregations() method
     */
    public function testGetAggregations()
    {
        $expectedResult = [
            'count' => 1,
            'max' => 1,
            'min' => 1,
            'std' => 1,
        ];
        $this->clientMock->expects($this->once())
            ->method('query')
            ->willReturn([
                'aggregations' => [
                    'prices' => [
                        'count' => 1,
                        'max' => 1,
                        'min' => 1,
                        'std_deviation' => 1,
                    ],
                ],
            ]);
        $this->assertEquals(
            $expectedResult,
            $this->model->getAggregations($this->entityStorage)
        );
    }

    /**
     * Test getInterval() method
     */
    public function testGetInterval()
    {
        $bucket = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $interval = $this->getMockBuilder('Magento\Framework\Search\Dynamic\IntervalInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->intervalFactory->expects($this->once())
            ->method('create')
            ->willReturn($interval);

        $this->assertEquals(
            $interval,
            $this->model->getInterval(
                $bucket,
                [$dimension],
                $this->entityStorage
            )
        );
    }

    /**
     * Test getAggregation() method
     */
    public function testGetAggregation()
    {
        $expectedResult = [
            1 => 1,
        ];
        $bucket = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn(1);

        $this->clientMock->expects($this->once())
            ->method('query')
            ->willReturn([
                'aggregations' => [
                    'prices' => [
                        'buckets' => [
                            [
                                'key' => 1,
                                'doc_count' => 1,
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertEquals(
            $expectedResult,
            $this->model->getAggregation(
                $bucket,
                [$dimension],
                10,
                $this->entityStorage
            )
        );
    }

    /**
     * Test prepareData() method
     */
    public function testPrepareData()
    {
        $expectedResult = [
            [
                'from' => '',
                'to' => 10,
                'count' => 1,
            ],
            [
                'from' => 10,
                'to' => '',
                'count' => 1,
            ],
        ];
        $this->assertEquals(
            $expectedResult,
            $this->model->prepareData(
                10,
                [
                    1 => 1,
                    2 => 1,
                ]
            )
        );
    }
}
