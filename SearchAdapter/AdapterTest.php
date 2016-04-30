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
 * @magentoAppIsolation enabled
 * magentoDataFixture Magento/Framework/Search/_files/products.php
 */
class AdapterTest extends \Magento\Framework\Search\Adapter\Mysql\AdapterTest
{
    /**
     * @var string
     */
    protected $searchEngine = Config::ENGINE_NAME;

    protected function setUp()
    {
        parent::setUp();
        $this->requestConfig = __DIR__ . '/../_files/requests.xml';
        // @todo add @ for magentoDataFixture when MAGETWO-44489 is done
        $this->markTestSkipped("Skipping until ES is configured on builds - MAGETWO-44489");
    }

    /**
     * @return \Magento\Framework\Search\AdapterInterface
     */
    protected function createAdapter()
    {
        return $this->objectManager->create('Magento\Elasticsearch\SearchAdapter\Adapter');
    }

    /**
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testMatchQuery()
    {
        parent::testMatchQuery();
    }

    /**
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testAggregationsQuery()
    {
        $this->markTestSkipped('Range query does not implemented.');
    }

    /**
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testMatchQueryFilters()
    {
        parent::testMatchQueryFilters();
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testRangeFilterWithAllFields()
    {
        parent::testRangeFilterWithAllFields();
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testRangeFilterWithoutFromField()
    {
        parent::testRangeFilterWithoutFromField();
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testRangeFilterWithoutToField()
    {
        parent::testRangeFilterWithoutToField();
    }

    /**
     * Term filter test
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testTermFilter()
    {
        parent::testTermFilter();
    }

    /**
     * Term filter test
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testTermFilterArray()
    {
        parent::testTermFilterArray();
    }

    /**
     * Term filter test
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testWildcardFilter()
    {
        parent::testWildcardFilter();
    }

    /**
     * Request limits test
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testSearchLimit()
    {
        parent::testSearchLimit();
    }

    /**
     * Bool filter test
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testBoolFilter()
    {
        parent::testBoolFilter();
    }

    /**
     * Test bool filter with nested negative bool filter
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testBoolFilterWithNestedNegativeBoolFilter()
    {
        parent::testBoolFilterWithNestedNegativeBoolFilter();
    }

    /**
     * Test range inside nested negative bool filter
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testBoolFilterWithNestedRangeInNegativeBoolFilter()
    {
        parent::testBoolFilterWithNestedRangeInNegativeBoolFilter();
    }

    /**
     * Sample Advanced search request test
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @dataProvider advancedSearchDataProvider
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
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testCustomFilterableAttribute()
    {
        parent::testCustomFilterableAttribute();
    }
}
