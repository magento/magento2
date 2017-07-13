<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Observer;

/**
 * Integration test for case, when customer is able to download
 * downloadable product, after order was canceled.
 */
class SetLinkStatusObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Initialization of dependencies
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Asserting, that links status is expired after canceling of order.
     * This test relates to the GitHub issue magento/magento2#8515.
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     */
    public function testCheckStatusOnOrderCancel()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $orderItems = $order->getAllItems();
        $items = array_values($orderItems);
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = array_shift($items);

        /** Canceling order to reproduce test case */
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
        $order->save();

        /** @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection $linkCollection */
        $linkCollection = $this->objectManager->create(
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory::class
        )->create();

        $linkCollection->addFieldToFilter('order_item_id', $orderItem->getId());

        /** Assert there are items in linkCollection to avoid false-positive test result. */
        $this->assertGreaterThan(0, $linkCollection->count());

        /** @var \Magento\Downloadable\Model\Link\Purchased\Item $linkItem */
        foreach ($linkCollection->getItems() as $linkItem) {
            $this->assertEquals(
                \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED,
                $linkItem->getStatus()
            );
        }
    }
}
