<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\DataProvider\Base;

use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Model\DataProvider\Suggestions as SuggestionsDataProvider;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Elasticsearch6\Model\Client\Elasticsearch;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryResult;
use Magento\Search\Model\QueryResultFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuggestionsTest extends TestCase
{
    /**
     * @var SuggestionsDataProvider
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var QueryResultFactory|MockObject
     */
    private $queryResultFactory;

    /**
     * @var ConnectionManager|MockObject
     */
    private $connectionManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var SearchIndexNameResolver|MockObject
     */
    private $searchIndexNameResolver;

    /**
     * @var StoreManager|MockObject
     */
    private $storeManager;

    /**
     * @var QueryInterface|MockObject
     */
    private $query;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isElasticsearchEnabled'])
            ->getMock();

        $this->queryResultFactory = $this->getMockBuilder(QueryResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->connectionManager = $this->getMockBuilder(ConnectionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->searchIndexNameResolver = $this
            ->getMockBuilder(SearchIndexNameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndexName'])
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->query = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\DataProvider\Base\Suggestions::class,
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

        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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

        $client = $this->getMockBuilder(Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($client);

        $client->expects($this->any())
            ->method('query')
            ->willReturn([
                'suggest' => [
                    'phrase_field' => [
                        'options' => [
                            'text' => 'query',
                            'score' => 1,
                            'freq' => 1,
                        ]
                    ],
                ],
            ]);

        $query = $this->getMockBuilder(QueryResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryResultFactory->expects($this->any())
            ->method('create')
            ->willReturn($query);

        $this->assertIsArray($this->model->getItems($this->query));
    }
}
