<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Report\Sold;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
     */
    private $collection;

    protected function setUp(): void
    {
        /**
         * @var \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
         */
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Reports\Model\ResourceModel\Product\Sold\Collection::class
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_item_with_configurable_for_reorder.php
     */
    public function testFilterByProductTypeException()
    {
        $items = $this->collection->addOrderedQty()->getItems();
        $this->assertCount(1, $items);
        $orderItem = array_shift($items);
        $this->assertEquals('1.0000', $orderItem['ordered_qty']);
        $this->assertEquals('Configurable Product', $orderItem['order_items_name']);
        //verify if order_item_sku exists in return data
        $this->assertEquals('simple_20', $orderItem['order_items_sku']);
    }
}
