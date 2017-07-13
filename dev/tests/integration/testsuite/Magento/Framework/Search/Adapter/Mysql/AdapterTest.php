<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Search\Model\EngineResolver;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class AdapterTest
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Framework/Search/_files/products.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    }

    /**
     * Get request config path
     *
     * @return string
     */
    protected function getRequestConfigPath()
    {
        return __DIR__ . '/../../_files/requests.xml';
    }

    /**
     * Make sure that correct engine is set
     */
    protected function assertPreConditions()
    {
        $currentEngine = $this->objectManager->get(MutableScopeConfigInterface::class)
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
     * @param \Magento\Framework\Search\Response\QueryResponse $queryResponse
     * @param array $expectedIds
     */
    private function assertProductIds($queryResponse, $expectedIds)
    {
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Api\Search\Document $document */
            $actualIds[] = $document->getId();
        }
        sort($actualIds);
        sort($expectedIds);
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * @magentoConfigFixture current_store catalog/search/engine mysql
     */
    public function testMatchQuery()
    {
        $this->requestBuilder->bind('fulltext_search_query', 'socks');
        $this->requestBuilder->setRequestName('one_match');

        $queryResponse = $this->executeQuery();

        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * Term filter test
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * Request limits test
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * Bool filter test
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * Test bool filter with nested negative bool filter
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * Test range inside nested negative bool filter
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
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
     * Sample Advanced search request test
     *
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @dataProvider advancedSearchDataProvider
     * @param string $nameQuery
     * @param string $descriptionQuery
     * @param array $rangeFilter
     * @param int $expectedRecordsCount
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
     * @magentoDataFixture Magento/Framework/Search/_files/filterable_attribute.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     */
    public function testCustomFilterableAttribute()
    {
        /** @var Attribute $attribute */
        $attribute = $this->objectManager->get(Attribute::class)
            ->loadByCode(Product::ENTITY, 'select_attribute');
        /** @var Collection $selectOptions */
        $selectOptions = $this->objectManager
            ->create(Collection::class)
            ->setAttributeFilter($attribute->getId());

        $attribute->loadByCode(Product::ENTITY, 'multiselect_attribute');
        /** @var Collection $multiselectOptions */
        $multiselectOptions = $this->objectManager
            ->create(Collection::class)
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
     * Data provider for testFilterByAttributeValues.
     *
     * @return array
     */
    public function filterByAttributeValuesDataProvider()
    {
        return [
            'quick_search_container' => [
                'quick_search_container',
                [
                    // Make sure search uses "should" cause.
                    'search_term' => 'Simple Product',
                ],
            ],
            'advanced_search_container' => [
                'advanced_search_container',
                [
                    // Make sure "wildcard" feature works.
                    'sku' => 'simple_product',
                ]
            ],
            'catalog_view_container' => [
                'catalog_view_container',
                [
                    'category_ids' => 2
                ]
            ]
        ];
    }

    /**
     * Test filtering by two attributes.
     *
     * @magentoDataFixture Magento/Framework/Search/_files/filterable_attributes.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @dataProvider filterByAttributeValuesDataProvider
     * @param string $requestName
     * @param array $additionalData
     * @return void
     */
    public function testFilterByAttributeValues($requestName, $additionalData)
    {
        /** @var Attribute $attribute */
        $attribute = $this->objectManager->get(Attribute::class)
            ->loadByCode(Product::ENTITY, 'select_attribute_1');
        /** @var Collection $selectOptions1 */
        $selectOptions1 = $this->objectManager
            ->create(Collection::class)
            ->setAttributeFilter($attribute->getId());
        $attribute->loadByCode(Product::ENTITY, 'select_attribute_2');
        /** @var Collection $selectOptions2 */
        $selectOptions2 = $this->objectManager
            ->create(Collection::class)
            ->setAttributeFilter($attribute->getId());
        $this->requestBuilder->bind('select_attribute_1', $selectOptions1->getLastItem()->getId());
        $this->requestBuilder->bind('select_attribute_2', $selectOptions2->getLastItem()->getId());
        // Binds for specific containers.
        foreach ($additionalData as $key => $value) {
            $this->requestBuilder->bind($key, $value);
        }
        $this->requestBuilder->setRequestName($requestName);
        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * Advanced search request using date product attribute
     *
     * @param $rangeFilter
     * @param $expectedRecordsCount
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
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     */
    public function testAdvancedSearchCompositeProductWithOutOfStockOption()
    {
        /** @var Attribute $attribute */
        $attribute = $this->objectManager->get(Attribute::class)
            ->loadByCode(Product::ENTITY, 'test_configurable');
        /** @var Collection $selectOptions */
        $selectOptions = $this->objectManager
            ->create(Collection::class)
            ->setAttributeFilter($attribute->getId());

        $firstOption = $selectOptions->getFirstItem();
        $firstOptionId = $firstOption->getId();
        $this->requestBuilder->bind('test_configurable', $firstOptionId);
        $this->requestBuilder->setRequestName('filter_out_of_stock_child');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(0, $queryResponse->count());

        $secondOption = $selectOptions->getLastItem();
        $secondOptionId = $secondOption->getId();
        $this->requestBuilder->bind('test_configurable', $secondOptionId);
        $this->requestBuilder->setRequestName('filter_out_of_stock_child');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable_with_disabled_child.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     */
    public function testAdvancedSearchCompositeProductWithDisabledChild()
    {
        /** @var Attribute $attribute */
        $attribute = $this->objectManager->get(Attribute::class)
            ->loadByCode(Product::ENTITY, 'test_configurable');
        /** @var Collection $selectOptions */
        $selectOptions = $this->objectManager
            ->create(Collection::class)
            ->setAttributeFilter($attribute->getId());

        $firstOption = $selectOptions->getFirstItem();
        $firstOptionId = $firstOption->getId();
        $this->requestBuilder->bind('test_configurable', $firstOptionId);
        $this->requestBuilder->setRequestName('filter_out_of_stock_child');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(0, $queryResponse->count());

        $secondOption = $selectOptions->getLastItem();
        $secondOptionId = $secondOption->getId();
        $this->requestBuilder->bind('test_configurable', $secondOptionId);
        $this->requestBuilder->setRequestName('filter_out_of_stock_child');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(0, $queryResponse->count());
    }

    /**
     * Test for search weight customization to ensure that search weight works correctly,
     * and affects search results.
     *
     * @magentoDataFixture Magento/Framework/Search/_files/search_weight_products.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     */
    public function testSearchQueryBoost()
    {
        $this->requestBuilder->bind('query', 'antarctica');
        $this->requestBuilder->setRequestName('search_boost');
        $queryResponse = $this->executeQuery();
        $this->assertEquals(2, $queryResponse->count());

        /** @var \Magento\Framework\Api\Search\DocumentInterface $products */
        $products = iterator_to_array($queryResponse);
        /*
         * Products now contain search query in two attributes which are boosted with the same value: 1
         * The search keyword (antarctica) is mentioned twice only in one of the products.
         * And, as both attributes have the same search weight and boost, we expect that
         * the product with doubled keyword should be prioritized by a search engine as a most relevant
         *  and therefore will be first in the search result.
         */
        $firstProduct = reset($products);
        $this->assertEquals(1222, $firstProduct->getId());
        $secondProduct = end($products);
        $this->assertEquals(1221, $secondProduct->getId());

        /** @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository */
        $productAttributeRepository = $this->objectManager->get(
            \Magento\Catalog\Api\ProductAttributeRepositoryInterface::class
        );

        /**
         * Now we're going to change search weight of one of the attributes to ensure that it will affect
         * how products are ordered in the search result
         */
        /** @var Attribute $attribute */
        $attribute = $productAttributeRepository->get('name');
        $attribute->setSearchWeight(20);
        $productAttributeRepository->save($attribute);

        $this->requestBuilder->bind('query', 'antarctica');
        $this->requestBuilder->setRequestName('search_boost_name');
        $queryResponse = $this->executeQuery();
        $this->assertEquals(2, $queryResponse->count());

        /** @var \Magento\Framework\Api\Search\DocumentInterface $products */
        $products = iterator_to_array($queryResponse);
        /*
         * As for the first case, we have two the same products.
         * One of them has search keyword mentioned twice in the field which has search weight 1.
         * However, we've changed the search weight of another attribute
         *   which has only one mention of the search keyword in another product.
         *
         * The case is mostly the same but search weight has been changed and we expect that
         *   less relevant (with only one mention) but more boosted (search weight = 20) product
         *   will be prioritized higher than more relevant, but less boosted product.
         */
        $firstProduct = reset($products);
        $this->assertEquals(1221, $firstProduct->getId());
        //$firstProduct
        $secondProduct = end($products);
        $this->assertEquals(1222, $secondProduct->getId());
    }

    public function dateDataProvider()
    {
        return [
            [['from' => '2000-01-01T00:00:00Z', 'to' => '2000-01-01T00:00:00Z'], 1], //Y-m-d
            [['from' => '2000-01-01T00:00:00Z', 'to' => ''], 1],
            [['from' => '1999-12-31T00:00:00Z', 'to' => '2000-01-01T00:00:00Z'], 1],
            [['from' => '2000-02-01T00:00:00Z', 'to' => ''], 0],
        ];
    }
}
