<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\Order;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryReservations\Model\CleanupReservationsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderOnNotDefaultSourceTest extends TestCase
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
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

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

    protected function setUp()
    {
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
        $this->cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->cartItemFactory = Bootstrap::getObjectManager()->get(CartItemInterfaceFactory::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
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
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws ValidationException
     */
    public function testPlaceOrderWithInStockProduct()
    {
        $this->markTestSkipped(
            'Fix Integration Test for placing Order https://github.com/magento-engcom/msi/issues/687'
        );
        $sku = 'SKU-1';
        $stockId = 10;
        $quoteItemQty = 4;
        $reservedDuringCheckoutQty = 1.5;

        $cart = $this->getCartByStockId($stockId);
        $product = $this->productRepository->get($sku);
        $cartItem =
            $this->cartItemFactory->create(
                [
                    'data' => [
                        CartItemInterface::KEY_SKU => $product->getSku(),
                        CartItemInterface::KEY_QTY => $quoteItemQty,
                        CartItemInterface::KEY_QUOTE_ID => $cart->getId(),
                        'product_id' => $product->getId(),
                        'product' => $product
                    ]
                ]
            );
        $cart->addItem($cartItem);
        $this->cartRepository->save($cart);

        $this->appendReservation($sku, -$reservedDuringCheckoutQty, $stockId);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->appendReservation($sku, $reservedDuringCheckoutQty, $stockId);
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
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws ValidationException
     */
    public function testPlaceOrderWithOutOffStockProduct()
    {
        $this->markTestSkipped(
            'Fix Integration Test for placing Order https://github.com/magento-engcom/msi/issues/687'
        );
        $sku = 'SKU-1';
        $stockId = 10;
        $quoteItemQty = 4;
        $reservedDuringCheckoutQty = 3.8;

        $cart = $this->getCartByStockId($stockId);
        $product = $this->productRepository->get($sku);
        $cartItem =
            $this->cartItemFactory->create(
                [
                    'data' => [
                        CartItemInterface::KEY_SKU => $product->getSku(),
                        CartItemInterface::KEY_QTY => $quoteItemQty,
                        CartItemInterface::KEY_QUOTE_ID => $cart->getId(),
                        'product_id' => $product->getId(),
                        'product' => $product
                    ]
                ]
            );
        $cart->addItem($cartItem);
        $this->cartRepository->save($cart);

        //append reservation during checkout to make laking quantity in stock
        $this->appendReservation($sku, -$reservedDuringCheckoutQty, $stockId);

        self::expectException(LocalizedException::class);
        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNull($orderId);

        //cleanup
        $this->appendReservation($sku, $reservedDuringCheckoutQty, $stockId);
    }

    /**
     * @param string $productSku
     * @param float $qty
     * @param int $stockId
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws ValidationException
     */
    private function appendReservation(string $productSku, float $qty, int $stockId)
    {
        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId($stockId)->setSku($productSku)->setQuantity($qty)->build(),
        ]);
    }

    /**
     * @param int $stockId
     * @return CartInterface
     * @throws NoSuchEntityException
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
        $this->orderRepository->delete($this->orderRepository->get($orderId));
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }
}
