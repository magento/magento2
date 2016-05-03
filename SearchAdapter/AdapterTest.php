<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Elasticsearch\Model\Config;

/**
 * Class AdapterTest
 *
 * @magentoDbIsolation disabled
 * magentoDataFixture Magento/Framework/Search/_files/products.php
 * 
 * Important: Please make sure that each integration test file works with unique elastic search index. In order to
 * achieve this, use @magentoConfigFixture to pass unique value for 'elasticsearch_index_prefix' for every test
 * method. E.g. '@magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest'
 */
class AdapterTest extends \Magento\Framework\Search\Adapter\Mysql\AdapterTest
{
    /**
     * @var string
     */
    protected $searchEngine = Config::ENGINE_NAME;

    /**
     * Get request config path
     *
     * @return string
     */
    protected function getRequestConfigPath()
    {
        return __DIR__ . '/../_files/requests.xml';
    }

    /**
     * @return \Magento\Framework\Search\AdapterInterface
     */
    protected function createAdapter()
    {
        return $this->objectManager->create('Magento\Elasticsearch\SearchAdapter\Adapter');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testMatchQuery()
    {
        parent::testMatchQuery();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testAggregationsQuery()
    {
        $this->markTestSkipped('Range query does not implemented.');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testMatchQueryFilters()
    {
        parent::testMatchQueryFilters();
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testRangeFilterWithAllFields()
    {
        parent::testRangeFilterWithAllFields();
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testRangeFilterWithoutFromField()
    {
        parent::testRangeFilterWithoutFromField();
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testRangeFilterWithoutToField()
    {
        parent::testRangeFilterWithoutToField();
    }

    /**
     * Term filter test
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testTermFilter()
    {
        parent::testTermFilter();
    }

    /**
     * Term filter test
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testTermFilterArray()
    {
        parent::testTermFilterArray();
    }

    /**
     * Term filter test
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testWildcardFilter()
    {
        parent::testWildcardFilter();
    }

    /**
     * Request limits test
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     * 
     */
    public function testSearchLimit()
    {
        parent::testSearchLimit();
    }

    /**
     * Bool filter test
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testBoolFilter()
    {
        parent::testBoolFilter();
    }

    /**
     * Test bool filter with nested negative bool filter
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testBoolFilterWithNestedNegativeBoolFilter()
    {
        parent::testBoolFilterWithNestedNegativeBoolFilter();
    }

    /**
     * Test range inside nested negative bool filter
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testBoolFilterWithNestedRangeInNegativeBoolFilter()
    {
        parent::testBoolFilterWithNestedRangeInNegativeBoolFilter();
    }

    /**
     * Sample Advanced search request test
     *
     * @dataProvider advancedSearchDataProvider
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testSimpleAdvancedSearch(
        $nameQuery,
        $descriptionQuery,
        $rangeFilter,
        $expectedRecordsCount
    ) {
        parent::testSimpleAdvancedSearch(
            $nameQuery,
            $descriptionQuery,
            $rangeFilter,
            $expectedRecordsCount
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix adaptertest
     * @magentoDataFixture Magento/Elasticsearch/_files/search_products.php
     */
    public function testCustomFilterableAttribute()
    {
        parent::testCustomFilterableAttribute();
    }
}
