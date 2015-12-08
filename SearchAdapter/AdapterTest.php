<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Elasticsearch\SearchAdapter\Adapter as ElasticsearchAdapter;
use Magento\Framework\Search\Request\Builder as RequestBuilder;
use Magento\Framework\ObjectManagerInterface;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ElasticsearchAdapter
     */
    private $adapter;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Setup method
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Search\Request\Config\Converter $converter */
        $converter = $this->objectManager->create('Magento\Framework\Search\Request\Config\Converter');
        $document = new \DOMDocument();
        $document->load(__DIR__ . '/_files/requests.xml');
        $requestConfig = $converter->convert($document);

        /** @var \Magento\Framework\Search\Request\Config $config */
        $config = $this->objectManager->create('Magento\Framework\Search\Request\Config');
        $config->merge($requestConfig);

        $this->requestBuilder = $this->objectManager->create(
            'Magento\Framework\Search\Request\Builder',
            ['config' => $config]
        );

        $this->adapter = $this->objectManager->create('Magento\Elasticsearch\SearchAdapter\Adapter');
    }

    /**
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    private function executeQuery()
    {
        $this->reindexAll();
        /** @var \Magento\Framework\Search\RequestInterface $queryRequest */
        $queryRequest = $this->requestBuilder->create();
        $queryResponse = $this->adapter->query($queryRequest);
        return $queryResponse;
    }

    /**
     * @throws \Exception
     * @return void
     */
    private function reindexAll()
    {
        /** @var \Magento\Indexer\Model\Indexer[] $indexerList */
        $indexerList = $this->objectManager->get('Magento\Indexer\Model\Indexer\CollectionFactory')
            ->create()
            ->getItems();
        foreach ($indexerList as $indexer) {
            $indexer->reindexAll();
        }
    }

    /**
     * Test match query
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testMatchQuery()
    {
        $this->requestBuilder->bind('fulltext_search_query', 'socks');
        $this->requestBuilder->setRequestName('one_match');
        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * Test query with aggregations
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testAggregationsQuery()
    {
        $this->markTestSkipped('Range query does not implemented.');
        $this->requestBuilder->bind('fulltext_search_query', 'peoples');
        $this->requestBuilder->setRequestName('one_aggregations');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(2, $queryResponse->count());
        $this->assertEquals(
            ['weight_bucket', 'price_bucket', 'dynamic_price'],
            $queryResponse->getAggregations()
                ->getBucketNames()
        );
    }

    /**
     * Test query with filters
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testMatchQueryFilters()
    {
        $this->requestBuilder->bind('fulltext_search_query', 'socks');
        $this->requestBuilder->bind('pidm_from', 11);
        $this->requestBuilder->bind('pidm_to', 17);
        $this->requestBuilder->bind('pidsh', 18);
        $this->requestBuilder->setRequestName('one_match_filters');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testRangeFilterWithAllFields()
    {
        $this->requestBuilder->bind('range_filter_from', 11);
        $this->requestBuilder->bind('range_filter_to', 16);
        $this->requestBuilder->setRequestName('range_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(3, $queryResponse->count());
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testRangeFilterWithoutFromField()
    {
        $this->requestBuilder->bind('range_filter_to', 18);
        $this->requestBuilder->setRequestName('range_filter_without_from_field');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(4, $queryResponse->count());
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testRangeFilterWithoutToField()
    {
        $this->requestBuilder->bind('range_filter_from', 14);
        $this->requestBuilder->setRequestName('range_filter_without_to_field');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(4, $queryResponse->count());
    }

    /**
     * Term filter test
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testTermFilter()
    {
        $this->requestBuilder->bind('request.price', 18);
        $this->requestBuilder->setRequestName('term_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
        $this->assertEquals(
            4,
            $queryResponse->getIterator()
                ->offsetGet(0)
                ->getId()
        );
    }

    /**
     * Term filter test
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testTermFilterArray()
    {
        $this->requestBuilder->bind('request.price', [16, 18]);
        $this->requestBuilder->setRequestName('term_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(2, $queryResponse->count());
    }

    /**
     * Term filter test
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testWildcardFilter()
    {
        $this->requestBuilder->bind('wildcard_filter', 'un');
        $this->requestBuilder->setRequestName('one_wildcard');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * Bool filter test
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testBoolFilter()
    {
        $expectedIds = [2, 3];
        $this->requestBuilder->bind('must_range_filter1_from', 12);
        $this->requestBuilder->bind('must_range_filter1_to', 22);
        $this->requestBuilder->bind('should_term_filter1', 12);
        $this->requestBuilder->bind('should_term_filter2', 14);
        $this->requestBuilder->bind('should_term_filter3', 16);
        $this->requestBuilder->bind('should_term_filter4', 18);
        $this->requestBuilder->bind('not_term_filter1', 12);
        $this->requestBuilder->bind('not_term_filter2', 18);
        $this->requestBuilder->setRequestName('bool_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(count($expectedIds), $queryResponse->count());
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Search\Document $document */
            $actualIds[] = $document->getId();
        }
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * Test bool filter with nested negative bool filter
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testBoolFilterWithNestedNegativeBoolFilter()
    {
        $expectedIds = [1];
        $this->requestBuilder->bind('not_range_filter_from', 14);
        $this->requestBuilder->bind('not_range_filter_to', 20);
        $this->requestBuilder->bind('nested_not_term_filter', 12);
        $this->requestBuilder->setRequestName('bool_filter_with_nested_bool_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(count($expectedIds), $queryResponse->count());
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Search\Document $document */
            $actualIds[] = $document->getId();
        }
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * Test range inside nested negative bool filter
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testBoolFilterWithNestedRangeInNegativeBoolFilter()
    {
        $expectedIds = [1, 5];
        $this->requestBuilder->bind('nested_must_range_filter_from', 14);
        $this->requestBuilder->bind('nested_must_range_filter_to', 18);
        $this->requestBuilder->setRequestName('bool_filter_with_range_in_nested_negative_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(count($expectedIds), $queryResponse->count());
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Search\Document $document */
            $actualIds[] = $document->getId();
        }
        sort($actualIds);
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * Sample Advanced search request test
     *
     * @dataProvider advancedSearchDataProvider
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testSimpleAdvancedSearch(
        $nameQuery,
        $descriptionQuery,
        $rangeFilter,
        $expectedRecordsCount
    ) {
        $this->requestBuilder->bind('name_query', $nameQuery);
        $this->requestBuilder->bind('description_query', $descriptionQuery);
        $this->requestBuilder->bind('request.from_price', $rangeFilter['from']);
        $this->requestBuilder->bind('request.to_price', $rangeFilter['to']);
        $this->requestBuilder->setRequestName('advanced_search_test');

        $queryResponse = $this->executeQuery();
        $this->assertEquals($expectedRecordsCount, $queryResponse->count());
    }

    /**
     * @return array
     */
    public function advancedSearchDataProvider()
    {
        return [
            ['white', 'shorts', ['from' => '16', 'to' => '18'], 0],
            ['white', 'shorts', ['from' => '12', 'to' => '18'], 1],
            ['black', 'tshirts', ['from' => '12', 'to' => '20'], 0],
            ['peoples', 'green', ['from' => '12', 'to' => '22'], 2],
        ];
    }

    /**
     * Request limits test
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoDataFixture Magento/Elasticsearch/SearchAdapter/_files/products.php
     */
    public function testSearchLimit()
    {
        $this->requestBuilder->bind('wildcard_filter', '*');
        $this->requestBuilder->setFrom(2);
        $this->requestBuilder->setSize(2);
        $this->requestBuilder->setRequestName('one_wildcard');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(2, $queryResponse->count());
    }
}
