<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\ResourceModel\Advanced;

/**
 * Test class for \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection.
 * @magentoDbIsolation disabled
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
     */
    private $advancedCollection;

    protected function setUp()
    {
        $this->advancedCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('\Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection');
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

    public function filtersDataProvider()
    {
        return [
            [['name' => ['like' => 'shorts'], 'description' => ['like' => 'green']], 1],
            [['name' => 'white', 'description' => '  '], 1],
            [['name' => '  ', 'description' => 'green'], 2],
            [['name' => '  ', 'description' => '   '], 0],
        ];
    }
}
