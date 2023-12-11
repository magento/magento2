<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleCatalogSearch\Model\SearchEngineVersionReader;

/**
 * Class AdapterTest
 *
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Framework/Search/_files/products.php
 *
 * Important: Please make sure that each integration test file works with unique elastic search index. In order to
 * achieve this, use @ magentoConfigFixture to pass unique value for index_prefix for every test
 * method. E.g. '@ magentoConfigFixture current_store catalog/search/elasticsearch7_index_prefix adaptertest'
 *
 * In ElasticSearch, a reindex is required if the test includes a new data fixture with new items to search, see
 * testAdvancedSearchDateField().
 * phpstan:ignore
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdapterTest extends \PHPUnit\Framework\TestCase
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
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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

        $indexer = $this->objectManager->create(\Magento\Indexer\Model\Indexer::class);
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
    }

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
        return $this->objectManager->create(\Magento\Search\Model\AdapterFactory::class)->create();
    }

    /**
     * Make sure that correct engine is set
     */
    protected function assertPreConditions(): void
    {
        $currentEngine = $this->objectManager->get(EngineResolverInterface::class)->getCurrentSearchEngine();
        $installedEngine = $this->objectManager->get(SearchEngineVersionReader::class)->getFullVersion();
        $this->assertEquals(
            $installedEngine,
            $currentEngine,
            sprintf(
                'Search engine configuration "%s" is not compatible with the installed version',
                $currentEngine
            )
        );
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
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function testMatchQuery()
    {
        $this->requestBuilder->bind('fulltext_search_query', 'socks');
        $this->requestBuilder->setRequestName('one_match');

        $queryResponse = $this->executeQuery();

        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testMatchOrderedQuery()
    {
        $this->markTestSkipped(
            'Elasticsearch not expected to order results by default. Test is skipped intentionally.'
        );
        $expectedIds = [8, 7, 6, 5, 2];

        //Verify that MySql randomized result of equal-weighted results
        //consistently ordered by entity_id after multiple calls
        $this->requestBuilder->bind('fulltext_search_query', 'shorts');
        $this->requestBuilder->setRequestName('one_match');
        $queryResponse = $this->executeQuery();

        $this->assertEquals(5, $queryResponse->count());
        $this->assertOrderedProductIds($queryResponse, $expectedIds);
    }

    /**
     * @param \Magento\Framework\Search\Response\QueryResponse $queryResponse
     * @param array $expectedIds
     */
    private function assertOrderedProductIds($queryResponse, $expectedIds)
    {
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Api\Search\Document $document */
            $actualIds[] = $document->getId();
        }
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function testRangeFilterWithAllFields()
    {
        $this->requestBuilder->bind('range_filter_from', 11);
        $this->requestBuilder->bind('range_filter_to', 17);
        $this->requestBuilder->setRequestName('range_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(3, $queryResponse->count());
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function testTermFilterArray()
    {
        $this->requestBuilder->bind('request.price', [17, 18]);
        $this->requestBuilder->setRequestName('term_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(2, $queryResponse->count());
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
     * Term filter test
     *
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function testBoolFilter()
    {
        $expectedIds = [2, 3];
        $this->requestBuilder->bind('must_range_filter1_from', 13);
        $this->requestBuilder->bind('must_range_filter1_to', 22);
        $this->requestBuilder->bind('should_term_filter1', 13);
        $this->requestBuilder->bind('should_term_filter2', 15);
        $this->requestBuilder->bind('should_term_filter3', 17);
        $this->requestBuilder->bind('should_term_filter4', 18);
        $this->requestBuilder->bind('not_term_filter1', 13);
        $this->requestBuilder->bind('not_term_filter2', 18);
        $this->requestBuilder->setRequestName('bool_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(count($expectedIds), $queryResponse->count());
        $this->assertProductIds($queryResponse, $expectedIds);
    }

    /**
     * Test bool filter with nested negative bool filter
     *
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function testBoolFilterWithNestedNegativeBoolFilter()
    {
        $expectedIds = [1];
        $this->requestBuilder->bind('not_range_filter_from', 14);
        $this->requestBuilder->bind('not_range_filter_to', 20);
        $this->requestBuilder->bind('nested_not_term_filter', 13);
        $this->requestBuilder->setRequestName('bool_filter_with_nested_bool_filter');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(count($expectedIds), $queryResponse->count());
        $this->assertProductIds($queryResponse, $expectedIds);
    }

    /**
     * Test range inside nested negative bool filter
     *
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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
     * @dataProvider elasticSearchAdvancedSearchDataProvider
     * @magentoAppIsolation enabled
     * @param string $nameQuery
     * @param string $descriptionQuery
     * @param array $rangeFilter
     * @param int $expectedRecordsCount
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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
     * Elastic Search specific data provider for advanced search test.
     *
     * The expected array is for Elastic Search is different that the one for MySQL
     * because sometimes more matches are returned. For instance, 3rd index below
     * will return 3 matches instead of 1 (which is what MySQL returns).
     *
     * @return array
     */
    public function elasticSearchAdvancedSearchDataProvider()
    {
        return [
            ['white', 'shorts', ['from' => '16', 'to' => '18'], 0],
            ['white', 'shorts',['from' => '12', 'to' => '18'], 1],
            ['black', 'tshirts', ['from' => '12', 'to' => '20'], 0],
            ['shorts', 'green', ['from' => '12', 'to' => '22'], 3],
            //Search with empty fields/values
            ['white', '  ', ['from' => '12', 'to' => '22'], 1],
            ['  ', 'green', ['from' => '12', 'to' => '22'], 2]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Framework/Search/_files/filterable_attribute.php
     */
    public function testCustomFilterableAttribute()
    {
        // Reindex Elastic Search since filterable_attribute data fixture added new fields to be indexed
        $this->reindexAll();
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
     * Test filtering by two attributes.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Framework/Search/_files/filterable_attributes.php
     * @dataProvider filterByAttributeValuesDataProvider
     * @param string $requestName
     * @param array $additionalData
     * @return void
     */
    public function testFilterByAttributeValues($requestName, $additionalData)
    {
        // Reindex Elastic Search since filterable_attribute data fixture added new fields to be indexed
        $this->reindexAll();
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
     * @magentoAppIsolation enabled
     * @dataProvider dateDataProvider
     */
    public function testAdvancedSearchDateField($rangeFilter, $expectedRecordsCount)
    {
        // Reindex Elastic Search since date_attribute data fixture added new fields to be indexed
        $this->reindexAll();
        $this->requestBuilder->bind('date.from', $rangeFilter['from']);
        $this->requestBuilder->bind('date.to', $rangeFilter['to']);
        $this->requestBuilder->setRequestName('advanced_search_date_field');

        $queryResponse = $this->executeQuery();
        $this->assertEquals($expectedRecordsCount, $queryResponse->count());
    }

    /**
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable.php
     * @magentoAppIsolation enabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
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

        $visibility = [
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        ];

        $firstOption = $selectOptions->getFirstItem();
        $firstOptionId = $firstOption->getId();
        $this->requestBuilder->bind('test_configurable', $firstOptionId);
        $this->requestBuilder->bind('visibility', $visibility);
        $this->requestBuilder->setRequestName('filter_out_of_stock_child');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(0, $queryResponse->count());

        $secondOption = $selectOptions->getLastItem();
        $secondOptionId = $secondOption->getId();
        $this->requestBuilder->bind('test_configurable', $secondOptionId);
        $this->requestBuilder->bind('visibility', $visibility);
        $this->requestBuilder->setRequestName('filter_out_of_stock_child');

        $queryResponse = $this->executeQuery();
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable_with_disabled_child.php
     * @magentoAppIsolation enabled
     */
    public function testAdvancedSearchCompositeProductWithDisabledChild()
    {
        // Reindex Elastic Search since date_attribute data fixture added new fields to be indexed
        $this->reindexAll();
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
     * @magentoDataFixture Magento/Framework/Search/_files/search_weight_products.php
     * @magentoAppIsolation enabled
     */
    public function testSearchQueryBoost()
    {
        // Reindex Elastic Search since date_attribute data fixture added new fields to be indexed
        $this->reindexAll();
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

    /**
     * Perform full reindex
     *
     * @return void
     */
    private function reindexAll()
    {
        $indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
    }

    /**
     * Date data provider
     *
     * @return array
     */
    public function dateDataProvider()
    {
        return [
            [['from' => '1999-12-31T00:00:00Z', 'to' => '2000-01-01T00:00:00Z'], 1],
            [['from' => '2000-02-01T00:00:00Z', 'to' => ''], 0],
        ];
    }

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
            ],
            'quick search by date' => [
                'quick_search_container',
                [
                    'search_term' => '2000-10-30',
                ],
            ],
        ];
    }
}
