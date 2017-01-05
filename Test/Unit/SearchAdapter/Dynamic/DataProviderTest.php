<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Dynamic;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider
     */
    protected $model;

    /**
     * @var Magento\Elasticsearch\SearchAdapter\ConnectionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionManager;

    /**
     * @var Magento\Elasticsearch\Model\Adapter\FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMapper;

    /**
     * @var Magento\Catalog\Model\Layer\Filter\Price\Range|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $range;

    /**
     * @var Magento\Framework\Search\Dynamic\IntervalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $intervalFactory;

    /**
     * @var Magento\Elasticsearch\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientConfig;

    /**
     * @var Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var Magento\Framework\Search\Dynamic\EntityStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityStorage;

    /**
     * @var Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var Magento\Elasticsearch\Model\Client\Elasticsearch|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchIndexNameResolver;

    /**
     * @var Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolver;

    /**
     * @var Magento\Framework\App\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeInterface;

    /**
     * A private helper for setUp method.
     * @return void
     */
    private function setUpMockObjects()
    {
        $this->connectionManager = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ConnectionManager::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->range = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Price\Range::class)
            ->setMethods(['getPriceRange'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->intervalFactory = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\IntervalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientConfig = $this->getMockBuilder(\Magento\Elasticsearch\Model\Config::class)
            ->setMethods([
                'getIndexName',
                'getEntityType',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->setMethods(['getCustomerGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityStorage = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\EntityStorage::class)
            ->setMethods(['getSource'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityStorage->expects($this->any())
            ->method('getSource')
            ->willReturn([1]);
        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
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
        $this->clientMock = $this->getMockBuilder(\Magento\Elasticsearch\Model\Client\Elasticsearch::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->clientMock);

        $this->fieldMapper = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchIndexNameResolver = $this
            ->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeResolver = $this->getMockForAbstractClass(
            \Magento\Framework\App\ScopeResolverInterface::class,
            [],
            '',
            false
        );

        $this->scopeInterface = $this->getMockForAbstractClass(
            \Magento\Framework\App\ScopeInterface::class,
            [],
            '',
            false
        );
    }

    /**
     * Setup method
     * @return void
     */
    protected function setUp()
    {
        $this->setUpMockObjects();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider::class,
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
                'scopeResolver' => $this->scopeResolver
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
        $dimensionValue = 1;
        $bucket = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $interval = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\IntervalInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
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
        $dimensionValue = 1;
        $expectedResult = [
            1 => 1,
        ];
        $bucket = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
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
