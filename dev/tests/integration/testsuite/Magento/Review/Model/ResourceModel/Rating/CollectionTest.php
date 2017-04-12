<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel\Rating;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Collection
     */
    protected $collection;

    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Review\Model\ResourceModel\Rating\Collection::class
        );
    }

    /**
     * @magentoDataFixture Magento/Review/_files/customer_review_with_rating.php
     */
    public function testAddEntitySummaryToItem()
    {
        $ratingData = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Registry::class)
            ->registry('rating_data');

        $result = $this->collection->addEntitySummaryToItem($ratingData->getEntityId(), $ratingData->getStoreId());
        $this->assertEquals($this->collection, $result);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testAddEntitySummaryToItemEmpty()
    {
        foreach ($this->collection->getItems() as $item) {
            $item->delete();
        }
        $this->collection->clear();
        $result = $this->collection->addEntitySummaryToItem(1, 1);
        $this->assertEquals($this->collection, $result);
    }

    public function testAddStoreData()
    {
        $this->collection->addStoreData();
    }

    public function testSetStoreFilter()
    {
        $this->collection->setStoreFilter(1);
    }
}
