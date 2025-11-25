<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\ResourceModel\Advanced;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection.
 * @magentoDbIsolation disabled
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
     */
    private $advancedCollection;

    protected function setUp(): void
    {
        $advanced = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\CatalogSearch\Model\Search\ItemCollectionProvider::class);
        $this->advancedCollection = $advanced->getCollection();
        $indexerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(IndexerRegistry::class);
        $indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();
    }

    /**
     * @dataProvider filtersDataProvider
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testLoadWithFilterNoFilters($filters, $expectedCount)
    {
        // addFieldsToFilter will load filters,
        //   then loadWithFilter will trigger _renderFiltersBefore code in Advanced/Collection
        $this->advancedCollection->addFieldsToFilter([$filters])->loadWithFilter();
        $items = $this->advancedCollection->getItems();
        $this->assertCount($expectedCount, $items);
    }

    public static function filtersDataProvider()
    {
        return [
            [['name' => ['like' => 'shorts'], 'description' => ['like' => 'green']], 1],
            [['name' => 'white', 'description' => '  '], 1],
            [['name' => '  ', 'description' => 'green'], 2],
            [['name' => '  ', 'description' => '   '], 0],
        ];
    }
}
