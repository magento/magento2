<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel\Review\Product;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Review/_files/different_reviews.php
     */
    public function testGetResultingIds()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Review\Model\ResourceModel\Review\Product\Collection'
        );
        $collection->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED);
        $actual = $collection->getResultingIds();
        $this->assertCount(2, $actual);
    }
}
