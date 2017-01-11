<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
}
