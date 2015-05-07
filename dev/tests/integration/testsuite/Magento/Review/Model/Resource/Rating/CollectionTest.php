<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Resource\Rating;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Review\Model\Resource\Rating\Collection
     */
    protected $collection;

    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Review\Model\Resource\Rating\Collection'
        );
    }

    /**
     * @magentoDataFixture Magento/Review/_files/customer_rating.php
     */
    public function testAddEntitySummaryToItem()
    {
        $ratingData = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Registry')
            ->registry('rating_data');
        $this->collection->addEntitySummaryToItem($ratingData->getEntityId(), $ratingData->getStoreId());
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
