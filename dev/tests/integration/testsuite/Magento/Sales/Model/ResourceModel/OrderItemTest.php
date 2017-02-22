<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

class OrderItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Verify that serialized order item data was unserialized after load
     *
     * @magentoDataFixture Magento/Catalog/_files/order_item_with_product_and_custom_options.php
     */
    public function testGetOrderItem()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $items = $order->getItemsCollection();
        $this->assertNotEquals(0, $items->getSize());
        foreach ($items as $item) {
            $info = $item->getDataByKey('product_options');
            $this->assertTrue(is_array($info));
        }
    }
}
