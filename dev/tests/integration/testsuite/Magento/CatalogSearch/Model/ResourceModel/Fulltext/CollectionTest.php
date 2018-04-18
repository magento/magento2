<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext;

/**
 * Test class for \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection.
 * @magentoDbIsolation disabled
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider filtersDataProviderSearch
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testLoadWithFilterSearch($request, $filters, $expectedCount)
    {
        $objManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var  \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $fulltextCollection */
        $fulltextCollection = $objManager->create(
            '\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection',
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
     * @magentoDataFixture Magento/Framework/Search/_files/products_with_the_same_search_score.php
     */
    public function testSearchResultsAreTheSameForSameRequests()
    {
        $howManySearchRequests = 3;
        $previousResult = null;

        $objManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        foreach (range(1, $howManySearchRequests) as $i) {
            /** @var  \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $fulltextCollection */
            $fulltextCollection = $objManager->create(
                \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class,
                ['searchRequestName' => 'quick_search_container']
            );

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
            ['catalog_view_container', ['visibility' => [2, 4]], 5],
        ];
    }
}
