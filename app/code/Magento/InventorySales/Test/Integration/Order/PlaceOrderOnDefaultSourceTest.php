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
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryReservations\Model\CleanupReservationsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderOnDefaultSourceTest extends TestCase
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

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
        $this->cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->cartItemFactory = Bootstrap::getObjectManager()->get(CartItemInterfaceFactory::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
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
        $quoteItemQty = 3.8;
        $reservedDuringCheckoutQty = 1.5;

        $cart = $this->getCart();
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

        $this->appendReservation($sku, -$reservedDuringCheckoutQty);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->appendReservation($sku, $reservedDuringCheckoutQty);
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
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
        $quoteItemQty = 5.5;
        $reservedDuringCheckoutQty = 4.9;

        $cart = $this->getCart();
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
        $this->appendReservation($sku, -$reservedDuringCheckoutQty);

        self::expectException(LocalizedException::class);
        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNull($orderId);

        //cleanup
        $this->appendReservation($sku, $reservedDuringCheckoutQty);
    }

    /**
     * @param string $productSku
     * @param float $qty
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    private function appendReservation(string $productSku, float $qty)
    {
        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(
                $this->defaultStockProvider->getId()
            )->setSku($productSku)->setQuantity($qty)->build(),
        ]);
    }

    /**
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    private function getCart(): CartInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('reserved_order_id', 'test_order_1')
            ->create();
        /** @var CartInterface $cart */
        $cart = current($this->cartRepository->getList($searchCriteria)->getItems());
        $cart->setStoreId(1);

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
