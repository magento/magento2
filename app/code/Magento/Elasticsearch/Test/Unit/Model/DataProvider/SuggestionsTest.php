<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\DataProvider\Suggestions;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Search\Model\QueryResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Search\Model\QueryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuggestionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Suggestions
     */
    private $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var QueryResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryResultFactory;

    /**
     * @var ConnectionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionManager;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var SearchIndexNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchIndexNameResolver;

    /**
     * @var StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var QueryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $query;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(\Magento\Elasticsearch\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isElasticsearchEnabled'])
            ->getMock();

        $this->queryResultFactory = $this->getMockBuilder(\Magento\Search\Model\QueryResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->connectionManager = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ConnectionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchIndexNameResolver = $this
            ->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndexName'])
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->query = $this->getMockBuilder(\Magento\Search\Model\QueryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\DataProvider\Suggestions::class,
            [
                'queryResultFactory' => $this->queryResultFactory,
                'connectionManager' => $this->connectionManager,
                'scopeConfig' => $this->scopeConfig,
                'config' => $this->config,
                'searchIndexNameResolver' => $this->searchIndexNameResolver,
                'storeManager' => $this->storeManager
            ]
        );
    }

    /**
     * Test getItems() method
     */
    public function testGetItems()
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn(1);

        $this->config->expects($this->any())
            ->method('isElasticsearchEnabled')
            ->willReturn(1);

        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $store->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->searchIndexNameResolver->expects($this->any())
            ->method('getIndexName')
            ->willReturn('magento2_product_1');

        $this->query->expects($this->any())
            ->method('getQueryText')
            ->willReturn('query');

        $client = $this->getMockBuilder(\Magento\Elasticsearch\Model\Client\Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($client);

        $client->expects($this->any())
            ->method('suggest')
            ->willReturn([
                'suggestions' => [
                    [
                        'options' => [
                            'query' => [
                                'text' => 'query',
                                'score' => 1,
                                'freq' => 1,
                            ],
                        ]
                    ],
                ],
            ]);

        $query = $this->getMockBuilder(\Magento\Search\Model\QueryResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryResultFactory->expects($this->any())
            ->method('create')
            ->willReturn($query);

        $this->assertInternalType('array', $this->model->getItems($this->query));
    }
}
