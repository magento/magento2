<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProduct\Test\Integration\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySales\Test\Integration\Order\PlaceOrderOnDefaultStockTest as PlaceOrderTest;

class PlaceOrderOnDefaultStockTest extends PlaceOrderTest
{
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/419537/scenarios/1620162
     */
    public function testPlaceOrderWithInStockProduct()
    {
        $groupedSku = 'grouped_in_stock';
        $simpleSku = 'simple_11';
        $quoteItemQty = 1;
        $cart = $this->getCart();

        $groupedProduct = $this->productRepository->get($groupedSku);
        $cartItem = $this->getCartItem($groupedProduct, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);

        $simpleProduct = $this->productRepository->get($simpleSku);
        $cartItem = $this->getCartItem($simpleProduct, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);

        $this->cartRepository->save($cart);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testPlaceOrderWithOutOffStockProduct()
    {
        $groupedSku = 'grouped_out_of_stock';
        $simpleSku = 'simple_11';
        $quoteItemQty = 200;
        $cart = $this->getCart();

        $groupedProduct = $this->productRepository->get($groupedSku);
        $cartItem = $this->getCartItem($groupedProduct, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);

        $simpleProduct = $this->productRepository->get($simpleSku);
        $cartItem = $this->getCartItem($simpleProduct, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);

        $this->cartRepository->save($cart);


        self::expectException(LocalizedException::class);
        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNull($orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/backorders 1
     */
    public function testPlaceOrderWithOutOffStockProductAndBackOrdersTurnedOn()
    {
        $groupedSku = 'grouped_out_of_stock';
        $simpleSku = 'simple_11';
        $quoteItemQty = 200;
        $cart = $this->getCart();

        $groupedProduct = $this->productRepository->get($groupedSku);
        $cartItem = $this->getCartItem($groupedProduct, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);

        $simpleProduct = $this->productRepository->get($simpleSku);
        $cartItem = $this->getCartItem($simpleProduct, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);

        $this->cartRepository->save($cart);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/manage_stock 0
     */
    public function testPlaceOrderWithOutOffStockProductAndManageStockTurnedOff()
    {
        $groupedSku = 'grouped_out_of_stock';
        $simpleSku = 'simple_11';
        $quoteItemQty = 200;
        $cart = $this->getCart();

        $groupedProduct = $this->productRepository->get($groupedSku);
        $cartItem = $this->getCartItem($groupedProduct, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);

        $simpleProduct = $this->productRepository->get($simpleSku);
        $cartItem = $this->getCartItem($simpleProduct, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);

        $this->cartRepository->save($cart);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }
}
