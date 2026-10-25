<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends TestCase
{
    use MockCreationTrait;
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
        $this->connectionManager = $this->createPartialMock(ConnectionManager::class, ['getConnection']);

        $this->range = $this->createPartialMock(Range::class, ['getPriceRange']);
        $this->intervalFactory = $this->createMock(IntervalFactory::class);
        $this->clientConfig = $this->createPartialMock(Config::class, ['getIndexPrefix', 'getEntityType']);
        $this->storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->customerSession = $this->createPartialMock(Session::class, ['getCustomerGroupId']);
        $this->entityStorage = $this->createPartialMock(EntityStorage::class, ['getSource']);
        $this->entityStorage->expects($this->any())
            ->method('getSource')
            ->willReturn([1]);
        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->storeMock = $this->createMock(StoreInterface::class);
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
            ->method('getIndexPrefix')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');
        $this->clientMock = $this->createPartialMockWithReflection(
            ClientInterface::class,
            ['testConnection', 'query', 'bulkQuery']
        );
        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->clientMock);

        $this->fieldMapper = $this->createMock(FieldMapperInterface::class);

        $this->searchIndexNameResolver = $this->createMock(SearchIndexNameResolver::class);

        $this->scopeResolver = $this->createMock(ScopeResolverInterface::class);

        $this->scopeInterface = $this->createMock(ScopeInterface::class);

        $this->queryContainer = $this->createPartialMock(QueryContainer::class, ['getQuery']);
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
        $this->clientMock->method('query')->willReturn([
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

    public function testGetAggregationsWithException()
    {
        $this->queryContainer->expects($this->once())
            ->method('getQuery')
            ->willReturn([]);

        $this->clientMock->expects($this->once())
            ->method('query')
            ->willThrowException(new \Exception());

        $result = $this->model->getAggregations($this->entityStorage);
        $this->assertIsArray($result);
    }

    /**
     * Test getInterval() method
     */
    public function testGetInterval()
    {
        $dimensionValue = 1;
        $bucket = $this->createMock(BucketInterface::class);
        $interval = $this->createMock(IntervalInterface::class);
        $dimension = $this->getMockBuilder(Dimension::class)
            ->onlyMethods(['getValue'])
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
        $bucket = $this->createMock(BucketInterface::class);
        $dimension = $this->getMockBuilder(Dimension::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->never())
            ->method('getValue');
        $this->scopeResolver->expects($this->never())
            ->method('getScope');
        $this->scopeInterface->expects($this->never())
            ->method('getId');

        // Set query results for this specific test
        $this->clientMock->method('query')->willReturn([
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

    public function testGetAggregationWithException()
    {
        $bucket = $this->createMock(BucketInterface::class);
        $dimension = $this->createMock(Dimension::class);

        $this->queryContainer->expects($this->once())
            ->method('getQuery')
            ->willReturn([]);

        $this->clientMock->expects($this->once())
            ->method('query')
            ->willThrowException(new \Exception());

        $result = $this->model->getAggregation($bucket, [$dimension], 10, $this->entityStorage);
        $this->assertIsArray($result);
    }

    /**
     * Test prepareData() method
     */
    public function testPrepareData()
    {
        $expectedResult = [
            [
                'from' => 0,
                'to' => 10,
                'count' => 1,
            ],
            [
                'from' => 10,
                'to' => 20,
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
