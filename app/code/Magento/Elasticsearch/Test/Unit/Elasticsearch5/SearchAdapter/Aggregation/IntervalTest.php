<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Elasticsearch5\SearchAdapter\Aggregation;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Aggregation\Interval;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Elasticsearch5\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;

/**
 * Test for Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Aggregation\Interval class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntervalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Interval
     */
    private $model;

    /**
     * @var ConnectionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionManager;

    /**
     * @var FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldMapper;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientConfig;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * @var ElasticsearchClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var SearchIndexNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchIndexNameResolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->connectionManager = $this->getMockBuilder(ConnectionManager::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldMapper = $this->getMockBuilder(FieldMapperInterface::class)
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
            ->getMock();
        $this->customerSession = $this->getMockBuilder(CustomerSession::class)
            ->setMethods(['getCustomerGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver = $this
            ->getMockBuilder(SearchIndexNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->clientMock = $this->getMockBuilder(ElasticsearchClient::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->clientMock);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            Interval::class,
            [
                'connectionManager' => $this->connectionManager,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'searchIndexNameResolver' => $this->searchIndexNameResolver,
                'fieldName' => 'price_0_1',
                'storeId' => 1,
                'entityIds' => [265, 313, 281],
            ]
        );
    }

    /**
     * @dataProvider loadParamsProvider
     * @param string $limit
     * @param string $offset
     * @param string $lower
     * @param string $upper
     * @param array $queryResult
     * @param array $expected
     * @return void
     */
    public function testLoad(
        string $limit,
        string $offset,
        string $lower,
        string $upper,
        array $queryResult,
        array $expected
    ): void {
        $this->processQuery($queryResult);

        $this->assertEquals(
            $expected,
            $this->model->load($limit, $offset, $lower, $upper)
        );
    }

    /**
     * @dataProvider loadPrevParamsProvider
     * @param string $data
     * @param string $index
     * @param string $lower
     * @param array $queryResult
     * @param array|bool $expected
     * @return void
     */
    public function testLoadPrev(string $data, string $index, string $lower, array $queryResult, $expected): void
    {
        $this->processQuery($queryResult);

        $this->assertEquals(
            $expected,
            $this->model->loadPrevious($data, $index, $lower)
        );
    }

    /**
     * @dataProvider loadNextParamsProvider
     * @param string $data
     * @param string $rightIndex
     * @param string $upper
     * @param array $queryResult
     * @param array|bool $expected
     * @return void
     */
    public function testLoadNext(string $data, string $rightIndex, string $upper, array $queryResult, $expected): void
    {
        $this->processQuery($queryResult);

        $this->assertEquals(
            $expected,
            $this->model->loadNext($data, $rightIndex, $upper)
        );
    }

    /**
     * @param array $queryResult
     * @return void
     */
    private function processQuery(array $queryResult): void
    {
        $this->searchIndexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('magento2_product_1');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('document');
        $this->clientMock->expects($this->any())
            ->method('query')
            ->willReturn($queryResult);
    }

    /**
     * @return array
     */
    public function loadParamsProvider(): array
    {
        return [
            [
                'limit' => '6',
                'offset' => '2',
                'lower' => '24',
                'upper' => '42',
                'queryResult' => [
                    'hits' => [
                        'hits' => [
                            [
                                'fields' => [
                                    'price_0_1' => [25],
                                ],
                            ],
                        ],
                    ],
                ],
                'expected' => [25],
            ],
        ];
    }

    /**
     * @return array
     */
    public function loadPrevParamsProvider(): array
    {
        return [
            [
                'data' => '24',
                'rightIndex' => '1',
                'upper' => '24',
                'queryResult' => [
                    'hits' => [
                        'total'=> '1',
                        'hits' => [
                            [
                                'fields' => [
                                    'price_0_1' => ['25'],
                                ],
                            ],
                        ],
                    ],
                ],
                'expected' => ['25.0'],
            ],
            [
                'data' => '24',
                'rightIndex' => '1',
                'upper' => '24',
                'queryResult' => [
                    'hits' => ['total'=> '0'],
                ],
                'expected' => false,
            ],
        ];
    }

    /**
     * @return array
     */
    public function loadNextParamsProvider(): array
    {
        return [
            [
                'data' => '24',
                'rightIndex' => '2',
                'upper' => '42',
                'queryResult' => [
                    'hits' => [
                        'total'=> '1',
                        'hits' => [
                            [
                                'fields' => [
                                    'price_0_1' => ['25'],
                                ],
                            ],
                        ],
                    ],
                ],
                'expected' => ['25.0'],
            ],
            [
                'data' => '24',
                'rightIndex' => '2',
                'upper' => '42',
                'queryResult' => [
                    'hits' => ['total'=> '0'],
                ],
                'expected' => false,
            ],
        ];
    }
}
