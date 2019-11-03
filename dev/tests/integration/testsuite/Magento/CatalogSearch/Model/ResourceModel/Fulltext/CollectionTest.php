<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext;

/**
 * Test class for \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection.
 * @magentoDbIsolation disabled
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider filtersDataProviderSearch
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @magentoConfigFixture default/catalog/search/engine mysql
     * @magentoAppIsolation enabled
     */
    public function testLoadWithFilterSearch($request, $filters, $expectedCount)
    {
        $objManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var  \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $fulltextCollection */
        $fulltextCollection = $objManager->create(
            \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class,
            ['searchRequestName' => $request]
        );
        foreach ($filters as $field => $value) {
            $fulltextCollection->addFieldToFilter($field, $value);
        }
        $fulltextCollection->loadWithFilter();
        $items = $fulltextCollection->getItems();
        $this->assertCount($expectedCount, $items);
    }

    /**
     * @dataProvider filtersDataProviderQuickSearch
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testLoadWithFilterQuickSearch($filters, $expectedCount)
    {
        $objManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $searchLayer = $objManager->create(\Magento\Catalog\Model\Layer\Search::class);
        /** @var  \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $fulltextCollection */
        $fulltextCollection = $searchLayer->getProductCollection();
        foreach ($filters as $field => $value) {
            $fulltextCollection->addFieldToFilter($field, $value);
        }
        $fulltextCollection->loadWithFilter();
        $items = $fulltextCollection->getItems();
        $this->assertCount($expectedCount, $items);
    }

    /**
     * @dataProvider filtersDataProviderCatalogView
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testLoadWithFilterCatalogView($filters, $expectedCount)
    {
        $objManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $searchLayer = $objManager->create(\Magento\Catalog\Model\Layer\Category::class);
        /** @var  \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $fulltextCollection */
        $fulltextCollection = $searchLayer->getProductCollection();
        foreach ($filters as $field => $value) {
            $fulltextCollection->addFieldToFilter($field, $value);
        }
        $fulltextCollection->loadWithFilter();
        $items = $fulltextCollection->getItems();
        $this->assertCount($expectedCount, $items);
    }

    /**
     * @magentoDataFixture Magento/Framework/Search/_files/products_with_the_same_search_score.php
     */
    public function testSearchResultsAreTheSameForSameRequests()
    {
        $howManySearchRequests = 3;
        $previousResult = null;

        $objManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        foreach (range(1, $howManySearchRequests) as $i) {
            $searchLayer = $objManager->create(\Magento\Catalog\Model\Layer\Search::class);
            /** @var  \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $fulltextCollection */
            $fulltextCollection = $searchLayer->getProductCollection();

            $fulltextCollection->addFieldToFilter('search_term', 'shorts');
            $fulltextCollection->setOrder('relevance');
            $fulltextCollection->load();
            $items = $fulltextCollection->getItems();
            $this->assertGreaterThan(
                0,
                count($items),
                sprintf("Search #%s result must not be empty", $i)
            );

            if ($previousResult) {
                $this->assertEquals(
                    $previousResult,
                    array_keys($items),
                    "Search result must be the same for the same requests"
                );
            }

            $previousResult = array_keys($items);
        }
    }

    public function filtersDataProviderSearch()
    {
        return [
            ['quick_search_container', ['search_term' => '  shorts'], 2],
            ['quick_search_container', ['search_term' => '   '], 0],
            ['catalog_view_container', ['category_ids' => 2], 5],
            ['catalog_view_container', ['category_ids' => 100001], 0],
            ['catalog_view_container', ['category_ids' => []], 0],
            ['catalog_view_container', [], 0],
        ];
    }

    public function filtersDataProviderQuickSearch()
    {
        return [
            [['search_term' => '  shorts'], 2],
            [['search_term' => 'nonexistent'], 0],
        ];
    }

    public function filtersDataProviderCatalogView()
    {
        return [
            [['category_ids' => 2], 5],
            [['category_ids' => 100001], 0],
            [['category_ids' => []], 5],
            [[], 5],
        ];
    }
}
