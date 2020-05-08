<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Dynamic;

use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Customer\Model\Session;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\Search\Dynamic\IntervalInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends TestCase
{
    /**
     * @var QueryContainer|MockObject
     */
    private $queryContainer;

    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var ConnectionManager|MockObject
     */
    protected $connectionManager;

    /**
     * @var FieldMapperInterface|MockObject
     */
    protected $fieldMapper;

    /**
     * @var Range|MockObject
     */
    protected $range;

    /**
     * @var IntervalFactory|MockObject
     */
    protected $intervalFactory;

    /**
     * @var Config|MockObject
     */
    protected $clientConfig;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var Session|MockObject
     */
    protected $customerSession;

    /**
     * @var EntityStorage|MockObject
     */
    protected $entityStorage;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

    /**
     * @var ClientInterface|MockObject
     */
    protected $clientMock;

    /**
     * @var SearchIndexNameResolver|MockObject
     */
    protected $searchIndexNameResolver;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    protected $scopeResolver;

    /**
     * @var ScopeInterface|MockObject
     */
    protected $scopeInterface;

    /**
     * A private helper for setUp method.
     * @return void
     */
    private function setUpMockObjects()
    {
        $this->connectionManager = $this->getMockBuilder(ConnectionManager::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->range = $this->getMockBuilder(Range::class)
            ->setMethods(['getPriceRange'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->intervalFactory = $this->getMockBuilder(IntervalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientConfig = $this->getMockBuilder(Config::class)
            ->setMethods([
                'getIndexName',
                'getEntityType',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->setMethods(['getCustomerGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityStorage = $this->getMockBuilder(EntityStorage::class)
            ->setMethods(['getSource'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityStorage->expects($this->any())
            ->method('getSource')
            ->willReturn([1]);
        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
        $this->clientMock = $this->getMockBuilder(ClientInterface::class)
            ->setMethods(['query', 'testConnection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->clientMock);

        $this->fieldMapper = $this->getMockBuilder(FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->searchIndexNameResolver = $this
            ->getMockBuilder(SearchIndexNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->queryContainer = $this->getMockBuilder(QueryContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuery'])
            ->getMock();
    }

    /**
     * Setup method
     * @return void
     */
    protected function setUp(): void
    {
        $this->setUpMockObjects();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            DataProvider::class,
            [
                'connectionManager' => $this->connectionManager,
                'fieldMapper' => $this->fieldMapper,
                'range' => $this->range,
                'intervalFactory' => $this->intervalFactory,
                'clientConfig' => $this->clientConfig,
                'storeManager' => $this->storeManager,
                'customerSession' => $this->customerSession,
                'searchIndexNameResolver' => $this->searchIndexNameResolver,
                'indexerId' => 'catalogsearch_fulltext',
                'scopeResolver' => $this->scopeResolver,
                'queryContainer' => $this->queryContainer,
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

        $this->queryContainer->expects($this->once())
            ->method('getQuery')
            ->willReturn([]);

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
        $dimensionValue = 1;
        $bucket = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $interval = $this->getMockBuilder(IntervalInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $dimension = $this->getMockBuilder(Dimension::class)
            ->setMethods(['getValue'])
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
        $bucket = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $dimension = $this->getMockBuilder(Dimension::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->never())
            ->method('getValue');
        $this->scopeResolver->expects($this->never())
            ->method('getScope');
        $this->scopeInterface->expects($this->never())
            ->method('getId');

        $this->clientMock->expects($this->once())
            ->method('query')
            ->with($this->callback(function ($query) {
                $histogramParams = $query['body']['aggregations']['prices']['histogram'];
                // Assert the interval is queried as a float. See MAGETWO-95471
                if ($histogramParams['interval'] !== 10.0) {
                    return false;
                }
                if (!isset($histogramParams['min_doc_count']) || $histogramParams['min_doc_count'] !== 1) {
                    return false;
                }
                return true;
            }))
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

        $this->queryContainer->expects($this->once())
            ->method('getQuery')
            ->willReturn([]);

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
