<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\Order;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\SaveStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderOnNotDefaultStockTest extends TestCase
{
    /**
     * @var CleanupReservationsInterface
     */
    private $cleanupReservations;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartItemInterfaceFactory
     */
    private $cartItemFactory;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var SaveStockItemConfigurationInterface
     */
    private $saveStockItemConfiguration;

    protected function setUp()
    {
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->cartItemFactory = Bootstrap::getObjectManager()->get(CartItemInterfaceFactory::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->orderManagement = Bootstrap::getObjectManager()->get(OrderManagementInterface::class);
        $this->getStockItemConfiguration =
            Bootstrap::getObjectManager()->get(GetStockItemConfigurationInterface::class);
        $this->saveStockItemConfiguration =
            Bootstrap::getObjectManager()->get(SaveStockItemConfigurationInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderWithInStockProduct()
    {
        $sku = 'SKU-2';
        $stockId = 30;
        $quoteItemQty = 2.2;

        $this->setStockItemConfigIsDecimal($sku, $stockId);
        $cart = $this->getCartByStockId($stockId);
        $product = $this->productRepository->get($sku);
        $cartItem = $this->getCartItem($product, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);
        $this->cartRepository->save($cart);
        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderWithOutOffStockProduct()
    {
        $sku = 'SKU-2';
        $stockId = 30;
        $quoteItemQty = 6.2;

        $this->setStockItemConfigIsDecimal($sku, $stockId);
        $cart = $this->getCartByStockId($stockId);
        $product = $this->productRepository->get($sku);
        $cartItem = $this->getCartItem($product, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);
        $this->cartRepository->save($cart);

        self::expectException(LocalizedException::class);
        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNull($orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture store_for_global_website_store cataloginventory/item_options/backorders 1
     *
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderWithOutOffStockProductAndBackOrdersTurnedOn()
    {
        $sku = 'SKU-2';
        $stockId = 30;
        $quoteItemQty = 6.5;

        $this->setStockItemConfigIsDecimal($sku, $stockId);
        $cart = $this->getCartByStockId($stockId);
        $product = $this->productRepository->get($sku);
        $cartItem = $this->getCartItem($product, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);
        $this->cartRepository->save($cart);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/manage_stock 0
     *
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderWithOutOffStockProductAndManageStockTurnedOff()
    {
        $sku = 'SKU-2';
        $stockId = 30;
        $quoteItemQty = 6.5;

        $this->setStockItemConfigIsDecimal($sku, $stockId);
        $cart = $this->getCartByStockId($stockId);
        $product = $this->productRepository->get($sku);
        $cartItem = $this->getCartItem($product, $quoteItemQty, (int)$cart->getId());
        $cart->addItem($cartItem);
        $this->cartRepository->save($cart);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @param int $stockId
     * @return CartInterface
     */
    private function getCartByStockId(int $stockId): CartInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('reserved_order_id', 'test_order_1')
            ->create();
        /** @var CartInterface $cart */
        $cart = current($this->cartRepository->getList($searchCriteria)->getItems());
        /** @var StockInterface $stock */
        $stock = $this->stockRepository->get($stockId);
        /** @var SalesChannelInterface[] $salesChannels */
        $salesChannels = $stock->getExtensionAttributes()->getSalesChannels();
        $storeCode = 'store_for_';
        foreach ($salesChannels as $salesChannel) {
            if ($salesChannel->getType() == SalesChannelInterface::TYPE_WEBSITE) {
                $storeCode .= $salesChannel->getCode();
                break;
            }
        }
        /** @var StoreInterface $store */
        $store = $this->storeRepository->get($storeCode);
        $this->storeManager->setCurrentStore($storeCode);
        $cart->setStoreId($store->getId());

        return $cart;
    }

    /**
     * @param int $orderId
     */
    private function deleteOrderById(int $orderId)
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->orderManagement->cancel($orderId);
        $this->orderRepository->delete($this->orderRepository->get($orderId));
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    /**
     * @param ProductInterface $product
     * @param float $quoteItemQty
     * @param int $cartId
     * @return CartItemInterface
     */
    private function getCartItem(ProductInterface $product, float $quoteItemQty, int $cartId): CartItemInterface
    {
        /** @var CartItemInterface $cartItem */
        $cartItem =
            $this->cartItemFactory->create(
                [
                    'data' => [
                        CartItemInterface::KEY_SKU => $product->getSku(),
                        CartItemInterface::KEY_QTY => $quoteItemQty,
                        CartItemInterface::KEY_QUOTE_ID => $cartId,
                        'product_id' => $product->getId(),
                        'product' => $product
                    ]
                ]
            );
        return $cartItem;
    }

    /**
     * @param $sku
     * @param $stockId
     */
    private function setStockItemConfigIsDecimal(string $sku, int $stockId): void
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $stockItemConfiguration->setIsQtyDecimal(true);
        $this->saveStockItemConfiguration->execute($sku, $stockId, $stockItemConfiguration);
    }

    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }
}
