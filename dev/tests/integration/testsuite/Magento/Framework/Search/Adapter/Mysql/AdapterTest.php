<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Search\Model\EngineResolver;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class AdapterTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Framework/Search/_files/products.php
 */
class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\AdapterInterface
     */
    private $adapter;

    /**
     * @var \Magento\Framework\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $searchEngine = EngineResolver::CATALOG_SEARCH_MYSQL_ENGINE;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Search\Request\Config\Converter $converter */
        $converter = $this->objectManager->create(\Magento\Framework\Search\Request\Config\Converter::class);

        $document = new \DOMDocument();
        $document->load($this->getRequestConfigPath());
        $requestConfig = $converter->convert($document);

        /** @var \Magento\Framework\Search\Request\Config $config */
        $config = $this->objectManager->create(\Magento\Framework\Search\Request\Config::class);
        $config->merge($requestConfig);

        $this->requestBuilder = $this->objectManager->create(
            \Magento\Framework\Search\Request\Builder::class,
            ['config' => $config]
        );

        $this->adapter = $this->createAdapter();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * Get request config path.
     * 
     * @return string
     */
    protected function getRequestConfigPath()
    {
        return __DIR__ . '/../../_files/requests.xml';
    }

    /**
     * Make sure that correct engine is set.
     *
     * @return void
     */
    protected function assertPreConditions()
    {
        $currentEngine = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
            ->getValue(EngineInterface::CONFIG_ENGINE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->assertEquals($this->searchEngine, $currentEngine);
    }

    /**
     * @return \Magento\Framework\Search\AdapterInterface
     */
    protected function createAdapter()
    {
        return $this->objectManager->create(\Magento\Framework\Search\Adapter\Mysql\Adapter::class);
    }

    /**
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    private function executeQuery()
    {
        /** @var \Magento\Framework\Search\RequestInterface $queryRequest */
        $queryRequest = $this->requestBuilder->create();

        $queryResponse = $this->adapter->query($queryRequest);

        return $queryResponse;
    }

    /**
     * Assert response product Ids match expected.
     *
     * @param \Magento\Framework\Search\Response\QueryResponse $queryResponse
     * @param array $expectedIds
     * @return void
     */
    private function assertProductIds($queryResponse, $expectedIds)
    {
        $actualIds = $this->getProductIds($queryResponse);
        sort($actualIds);
        sort($expectedIds);
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * Returns document ids from query response.
     *
     * @param \Magento\Framework\Search\Response\QueryResponse $queryResponse
     * @return array
     */
    protected function getProductIds(\Magento\Framework\Search\Response\QueryResponse $queryResponse)
    {
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Api\Search\Document $document */
            $actualIds[] = $document->getId();
        }

        return $actualIds;
    }

    /**
     * Match query test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testMatchQuery()
    {
        $this->requestBuilder->bind('fulltext_search_query', 'socks');
        $this->requestBuilder->setRequestName('one_match');

        $queryResponse = $this->executeQuery();

        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * Query aggregation test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testAggregationsQuery()
    {
        $this->requestBuilder->bind('fulltext_search_query', 'peoples');
        $this->requestBuilder->setRequestName('one_aggregations');

        $queryResponse = $this->executeQuery();

        $this->assertEquals(2, $queryResponse->count());
        $this->assertEquals(
            ['weight_bucket', 'price_bucket', 'dynamic_price'],
            $queryResponse->getAggregations()->getBucketNames()
        );
    }

    /**
     * Match query filter test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
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
     * Range filter test with all fields filled.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
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
     * Range filter test without from field filled.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testRangeFilterWithoutFromField()
    {
        $this->requestBuilder->bind('range_filter_to', 18);
        $this->requestBuilder->setRequestName('range_filter_without_from_field');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(4, $queryResponse->count());
    }

    /**
     * Range filter test without to field filled.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testRangeFilterWithoutToField()
    {
        $this->requestBuilder->bind('range_filter_from', 14);
        $this->requestBuilder->setRequestName('range_filter_without_to_field');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(4, $queryResponse->count());
    }

    /**
     * Term filter test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testTermFilter()
    {
        $this->requestBuilder->bind('request.price', 18);
        $this->requestBuilder->setRequestName('term_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
        $this->assertEquals(4, $queryResponse->getIterator()->offsetGet(0)->getId());
    }

    /**
     * Term filter test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testTermFilterArray()
    {
        $this->requestBuilder->bind('request.price', [16, 18]);
        $this->requestBuilder->setRequestName('term_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(2, $queryResponse->count());
    }

    /**
     * Wildcard filter test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testWildcardFilter()
    {
        $expectedIds = [1, 3, 5];
        $this->requestBuilder->bind('wildcard_filter', 're');
        $this->requestBuilder->setRequestName('one_wildcard');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(3, $queryResponse->count());
        $this->assertProductIds($queryResponse, $expectedIds);
    }

    /**
     * Request limits test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testSearchLimit()
    {
        $this->requestBuilder->bind('wildcard_filter', 're');
        $this->requestBuilder->setFrom(1);
        $this->requestBuilder->setSize(2);
        $this->requestBuilder->setRequestName('one_wildcard');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(2, $queryResponse->count());
    }

    /**
     * Bool filter test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
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
        $this->assertProductIds($queryResponse, $expectedIds);
    }

    /**
     * Test bool filter with nested negative bool filter.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
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
        $this->assertProductIds($queryResponse, $expectedIds);
    }

    /**
     * Test range inside nested negative bool filter.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testBoolFilterWithNestedRangeInNegativeBoolFilter()
    {
        $expectedIds = [1, 5];
        $this->requestBuilder->bind('nested_must_range_filter_from', 14);
        $this->requestBuilder->bind('nested_must_range_filter_to', 18);
        $this->requestBuilder->setRequestName('bool_filter_with_range_in_nested_negative_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(count($expectedIds), $queryResponse->count());
        $this->assertProductIds($queryResponse, $expectedIds);
    }

    /**
     * Sample Advanced search request test.
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @dataProvider advancedSearchDataProvider
     * @return void
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
            ['white', 'shorts',['from' => '12', 'to' => '18'], 1],
            ['black', 'tshirts', ['from' => '12', 'to' => '20'], 0],
            ['shorts', 'green', ['from' => '12', 'to' => '22'], 1],
            //Search with empty fields/values
            ['white', '  ', ['from' => '12', 'to' => '22'], 1],
            ['  ', 'green', ['from' => '12', 'to' => '22'], 2]
        ];
    }

    /**
     * Custom filterable attributes test.
     *
     * @magentoDataFixture Magento/Framework/Search/_files/filterable_attribute.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testCustomFilterableAttribute()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'select_attribute');
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $selectOptions */
        $selectOptions = $this->objectManager
            ->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class)
            ->setAttributeFilter($attribute->getId());

        $attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'multiselect_attribute');
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $multiselectOptions */
        $multiselectOptions = $this->objectManager
            ->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class)
            ->setAttributeFilter($attribute->getId());

        $this->requestBuilder->bind('select_attribute', $selectOptions->getLastItem()->getId());
        $this->requestBuilder->bind('multiselect_attribute', $multiselectOptions->getLastItem()->getId());
        $this->requestBuilder->bind('price.from', 98);
        $this->requestBuilder->bind('price.to', 100);
        $this->requestBuilder->bind('category_ids', 2);
        $this->requestBuilder->setRequestName('filterable_custom_attributes');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * Advanced search request using date product attribute.
     *
     * @param $rangeFilter
     * @param $expectedRecordsCount
     * @return void
     * @magentoDataFixture Magento/Framework/Search/_files/date_attribute.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @dataProvider dateDataProvider
     */
    public function testAdvancedSearchDateField($rangeFilter, $expectedRecordsCount)
    {
        $this->requestBuilder->bind('date.from', $rangeFilter['from']);
        $this->requestBuilder->bind('date.to', $rangeFilter['to']);
        $this->requestBuilder->setRequestName('advanced_search_date_field');

        $queryResponse = $this->executeQuery();
        $this->assertEquals($expectedRecordsCount, $queryResponse->count());
    }

    /**
     * Tests configurable product search with out of stock option.
     *
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @return void
     */
    public function testAdvancedSearchConfigProductWithOutOfStockOption()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'test_configurable_searchable');
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $selectOptions */
        $selectOptions = $this->objectManager
            ->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class)
            ->setAttributeFilter($attribute->getId());

        $firstOption = $selectOptions->getFirstItem();
        $firstOptionId = $firstOption->getId();
        $this->requestBuilder->bind('test_configurable_searchable', $firstOptionId);
        $this->requestBuilder->setRequestName('filter_out_of_stock_child');

        $queryResponse = $this->executeQuery();

        $this->assertEquals(0, $queryResponse->count());

        $secondOption = $selectOptions->getLastItem();
        $secondOptionId = $secondOption->getId();
        $this->requestBuilder->bind('test_configurable_searchable', $secondOptionId);
        $this->requestBuilder->setRequestName('filter_out_of_stock_child');

        $queryResponse = $this->executeQuery();

        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * @return array
     */
    public function dateDataProvider()
    {
        return [
            [['from' => '2000-01-01T00:00:00Z', 'to' => '2000-01-01T00:00:00Z'], 1], //Y-m-d
            [['from' => '2000-01-01T00:00:00Z', 'to' => ''], 1],
            [['from' => '1999-12-31T00:00:00Z', 'to' => '2000-01-01T00:00:00Z'], 1],
            [['from' => '2000-02-01T00:00:00Z', 'to' => ''], 0],
        ];
    }

    /**
     * Search request using custom price attribute.
     *
     * @param $rangeFilter
     * @param $expectedRecordsCount
     * @return void
     * @magentoDataFixture Magento/Framework/Search/_files/price_attribute.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @dataProvider priceDataProvider
     */
    public function testSearchCustomPriceField($rangeFilter, $expectedRecordsCount)
    {
        $this->requestBuilder->bind('price.from', $rangeFilter['from']);
        $this->requestBuilder->bind('price.to', $rangeFilter['to']);
        $this->requestBuilder->setRequestName('search_custom_price_field');

        $queryResponse = $this->executeQuery();
        $this->assertEquals($expectedRecordsCount, $queryResponse->count());
    }

    /**
     * @return array
     */
    public function priceDataProvider()
    {
        return [
            [['from' => '19.8900', 'to' => '19.8900'], 1],
            [['from' => '19.8900', 'to' => ''], 1],
            [['from' => '19.0000', 'to' => '19.8900'], 1],
            [['from' => '', 'to' => '19.8900'], 1],
        ];
    }

    /**
     * Filter by tax class.
     *
     * @magentoDataFixture Magento/Framework/Search/_files/grouped_product.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     *
     * @return void
     */
    public function testFilterByTaxClass()
    {
        $groupedProduct = $this->productRepository->get('grouped-product');
        $simpleProduct = $this->productRepository->get('grouped-association-2');

        $this->requestBuilder->bind('term', $simpleProduct->getTaxClassId());
        $this->requestBuilder->setRequestName('tax_class_id_filter_query');

        $queryResponse = $this->executeQuery();
        $result = $this->getProductIds($queryResponse);

        self::assertCount(2, $result);

        self::assertContains($groupedProduct->getId(), $result, 'Grouped product not found by tax class.');
        self::assertContains($simpleProduct->getId(), $result, 'Simple product not found by tax class.');
    }
}
