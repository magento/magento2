<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Review\Product;

/**
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Review\Customer\Collection
     */
    private $collection;

    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Reports\Model\ResourceModel\Review\Customer\Collection::class
        );
    }

    /**
     * This tests covers issue described in:
     * https://github.com/magento/magento2/issues/10301
     *
     * @magentoDataFixture Magento/Review/_files/customer_review.php
     */
    public function testSelectCountSql()
    {
        $this->collection->addFieldToFilter('customer_name', ['like' => '%john%']);
        $this->assertEquals(1, $this->collection->getSize());
    }
}
