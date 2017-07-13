<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Reorder;

use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class UnavailableProductsProviderTest
 */
class UnavailableProductsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/order_item_with_configurable_for_reorder.php
     */
    public function testGetForOrder()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Sales\Model\OrderRepository $orderRepository */
        $orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $orderRepository->get(1);
        $orderItems = $order->getItems();
        $orderItemSimple = $orderItems[2];
        $orderItemSimple->getSku();
        /** @var UnavailableProductsProvider $unavailableProductsProvider */
        $unavailableProductsProvider =
            $objectManager->create(UnavailableProductsProvider::class);
        $unavailableProducts = $unavailableProductsProvider->getForOrder($order);
        $this->assertEquals($orderItemSimple->getSku(), $unavailableProducts[0]);
    }
}
