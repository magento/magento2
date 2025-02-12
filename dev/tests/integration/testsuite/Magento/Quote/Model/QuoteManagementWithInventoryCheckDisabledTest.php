<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

class QuoteManagementWithInventoryCheckDisabledTest extends TestCase
{
    private const PURCHASE_ORDER_NUMBER = '12345678';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /** @var ProductRepositoryInterface $productRepository */
    private $productRepository;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->get(CartManagementInterface::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
    }

    /**
     * Test order placement with disabled inventory check, different quantity and out of stock status.
     *
     * @param int $qty
     * @param int $stockStatus
     * @return void
     * @magentoDataFixture Magento/Sales/_files/quote_with_purchase_order.php
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @dataProvider getQtyAndStockStatusProvider
     */
    public function testPlaceOrderWithDisabledInventoryCheck(int $qty, int $stockStatus): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_1');
        $quote->getPayment()->setPoNumber(self::PURCHASE_ORDER_NUMBER);
        $quote->collectTotals()->save();

        /** @var ProductInterface $product */
        $product = $this->productRepository->get($quote->getItems()[0]->getSku(), false, null, true);

        $this->productRepository->save($product);

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem->setQty($qty);
        $stockItem->setIsInStock($stockStatus);

        /** @var StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $this->objectManager->get(StockItemRepositoryInterface::class);
        $stockItemRepository->save($stockItem);

        $this->expectException(LocalizedException::class);
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * @return array
     */
    public static function getQtyAndStockStatusProvider(): array
    {
        return [
            [0, 0],
            [100, 0],
        ];
    }

    /**
     * Test order placement with disabled inventory check, positive quantity and in stock status.
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_purchase_order.php
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @return void
     */
    public function testSaveWithPositiveQuantityAndInStockWithInventoryCheckDisabled(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_1');
        $quote->getPayment()->setPoNumber(self::PURCHASE_ORDER_NUMBER);
        $quote->collectTotals()->save();

        /** @var ProductInterface $product */
        $product = $this->productRepository->get($quote->getItems()[0]->getSku(), false, null, true);

        $this->productRepository->save($product);

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(100);
        $stockItem->setIsInStock(1);

        /** @var StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $this->objectManager->get(StockItemRepositoryInterface::class);
        $stockItemRepository->save($stockItem);

        $orderId = $this->cartManagement->placeOrder($quote->getId());;
        $order = $this->orderRepository->get($orderId);
        $orderItems = $order->getItems();
        $this->assertCount(1, $orderItems);
    }
}
