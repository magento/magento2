<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Aggregation;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Interval;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntervalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Interval
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
     * @var ElasticsearchClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var SearchIndexNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchIndexNameResolver;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->connectionManager = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ConnectionManager::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldMapper = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class)
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
        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver = $this
            ->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver::class)
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
        $this->clientMock = $this->getMockBuilder(\Magento\Elasticsearch\Model\Client\Elasticsearch::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->clientMock);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\Aggregation\Interval::class,
            [
                'connectionManager' => $this->connectionManager,
                'fieldMapper' => $this->fieldMapper,
                'clientConfig' => $this->clientConfig,
                'searchIndexNameResolver' => $this->searchIndexNameResolver,
                'fieldName' => 'price_0_1',
                'storeId' => 1,
                'entityIds' => [265, 313, 281]
            ]
        );
    }

    /**
     * @dataProvider loadParamsProvider
     * @param string $limit
     * @param string $offset
     * @param string $lower
     * @param string $upper
     * Test load() method
     */
    public function testLoad($limit, $offset, $lower, $upper)
    {
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('magento2_product_1');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('document');

        $expectedResult = [25];

        $this->clientMock->expects($this->once())
            ->method('query')
            ->willReturn([
                'hits' => [
                    'hits' => [
                        [
                            'fields' => [

                                'price_0_1' => [25],

                            ],
                        ],
                    ],
                ],
            ]);
        $this->assertEquals(
            $expectedResult,
            $this->model->load($limit, $offset, $lower, $upper)
        );
    }

    /**
     * @dataProvider loadPrevParamsProvider
     * @param string $data
     * @param string $index
     * @param string $lower
     * Test loadPrevious() method with offset
     */
    public function testLoadPrevArray($data, $index, $lower)
    {
        $queryResult = [
            'hits' => [
                'total'=> '1',
                'hits' => [
                    [
                        'fields' => [
                            'price_0_1' => ['25']
                        ]
                    ],
                ],
            ],
        ];

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('magento2_product_1');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('document');

        $expectedResult = ['25.0'];

        $this->clientMock->expects($this->any())
            ->method('query')
            ->willReturn($queryResult);
        $this->assertEquals(
            $expectedResult,
            $this->model->loadPrevious($data, $index, $lower)
        );
    }

    /**
     * @dataProvider loadPrevParamsProvider
     * @param string $data
     * @param string $index
     * @param string $lower
     * Test loadPrevious() method without offset
     */
    public function testLoadPrevFalse($data, $index, $lower)
    {
        $queryResult = ['hits' => ['total'=> '0']];

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('magento2_product_1');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('document');

        $this->clientMock->expects($this->any())
            ->method('query')
            ->willReturn($queryResult);
        $this->assertFalse(
            $this->model->loadPrevious($data, $index, $lower)
        );
    }

    /**
     * @dataProvider loadNextParamsProvider
     * @param string $data
     * @param string $rightIndex
     * @param string $upper
     * Test loadNext() method with offset
     */
    public function testLoadNextArray($data, $rightIndex, $upper)
    {
        $queryResult = [
            'hits' => [
                'total'=> '1',
                'hits' => [
                    [
                        'fields' => [
                            'price_0_1' => ['25']
                        ]
                    ],
                ],
            ]
        ];

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('magento2_product_1');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('document');

        $expectedResult = ['25.0'];

        $this->clientMock->expects($this->any())
            ->method('query')
            ->willReturn($queryResult);
        $this->assertEquals(
            $expectedResult,
            $this->model->loadNext($data, $rightIndex, $upper)
        );
    }

    /**
     * @dataProvider loadNextParamsProvider
     * @param string $data
     * @param string $rightIndex
     * @param string $upper
     * Test loadNext() method without offset
     */
    public function testLoadNextFalse($data, $rightIndex, $upper)
    {
        $queryResult = ['hits' => ['total'=> '0']];

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('magento2_product_1');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('document');

        $this->clientMock->expects($this->any())
            ->method('query')
            ->willReturn($queryResult);
        $this->assertFalse(
            $this->model->loadNext($data, $rightIndex, $upper)
        );
    }

    /**
     * @return array
     */
    public static function loadParamsProvider()
    {
        return [
            ['6', '2', '24', '42'],
        ];
    }

    /**
     * @return array
     */
    public static function loadPrevParamsProvider()
    {
        return [
            ['24', '1', '24'],
        ];
    }

    /**
     * @return array
     */
    public static function loadNextParamsProvider()
    {
        return [
            ['24', '2', '42'],
        ];
    }
}
