<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class for testing QuoteManagement model
 */
class QuoteManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create order with product that has child items
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     */
    public function testSubmit()
    {
        /**
         * Preconditions:
         * Load quote with Bundle product that has at least two child products
         */
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');

        /** Execute SUT */
        /** @var \Magento\Quote\Api\CartManagementInterface $model */
        $cartManagement = $objectManager->create(\Magento\Quote\Api\CartManagementInterface::class);
        /** @var \Magento\Sales\Api\OrderRepositoryInterface $orderRepository */
        $orderRepository = $objectManager->create(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $orderId = $cartManagement->placeOrder($quote->getId());
        $order = $orderRepository->get($orderId);

        /** Check if SUT caused expected effects */
        $orderItems = $order->getItems();
        $this->assertCount(3, $orderItems);
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == Type::TYPE_SIMPLE) {
                $this->assertNotEmpty($orderItem->getParentItem(), 'Parent is not set for child product');
                $this->assertNotEmpty($orderItem->getParentItemId(), 'Parent is not set for child product');
            }
        }
    }

    /**
     * Create order with product that has child items and one of them was deleted
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     */
    public function testSubmitWithDeletedItem()
    {
        /**
         * Preconditions:
         * Load quote with Bundle product that have at least to child products
         */
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple-2');
        $productRepository->delete($product);
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');

        /** Execute SUT */
        /** @var \Magento\Quote\Api\CartManagementInterface $model */
        $cartManagement = $objectManager->create(\Magento\Quote\Api\CartManagementInterface::class);
        /** @var \Magento\Sales\Api\OrderRepositoryInterface $orderRepository */
        $orderRepository = $objectManager->create(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $orderId = $cartManagement->placeOrder($quote->getId());
        $order = $orderRepository->get($orderId);

        /** Check if SUT caused expected effects */
        $orderItems = $order->getItems();
        $this->assertCount(2, $orderItems);
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == Type::TYPE_SIMPLE) {
                $this->assertNotEmpty($orderItem->getParentItem(), 'Parent is not set for child product');
                $this->assertNotEmpty($orderItem->getParentItemId(), 'Parent is not set for child product');
            }
        }
    }
}
