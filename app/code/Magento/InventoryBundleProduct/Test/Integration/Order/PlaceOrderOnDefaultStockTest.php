<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleProduct\Test\Integration\Order;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\SaveStockItemConfigurationInterface;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/default_stock_bundle_products.php
 * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
 * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
 */
class PlaceOrderOnDefaultStockTest extends TestCase
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
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var SaveStockItemConfigurationInterface
     */
    private $saveStockItemConfiguration;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->cartItemFactory = Bootstrap::getObjectManager()->get(CartItemInterfaceFactory::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->orderManagement = Bootstrap::getObjectManager()->get(OrderManagementInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->getStockItemConfiguration = Bootstrap::getObjectManager()
            ->get(GetStockItemConfigurationInterface::class);
        $this->saveStockItemConfiguration = Bootstrap::getObjectManager()
            ->get(SaveStockItemConfigurationInterface::class);
    }

    public function testPlaceOrderWithInStockProduct()
    {
        $bundleSku = 'bundle-product-in-stock';
        $qty = 3;
        $cart = $this->getCart();

        $bundleProduct = $this->productRepository->get($bundleSku);
        $cart->addProduct($bundleProduct, $this->getBuyRequest($bundleProduct, $qty));

        $this->cartRepository->save($cart);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Product that you are trying to add is not available.
     */
    public function testPlaceOrderWithOutOfStockProduct()
    {
        $bundleSku = 'bundle-product-out-of-stock';
        $qty = 3;
        $cart = $this->getCart();

        $bundleProduct = $this->productRepository->get($bundleSku);

        $cart->addProduct($bundleProduct, $this->getBuyRequest($bundleProduct, $qty));
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/backorders 1
     */
    public function testPlaceOrderWithOutOfStockProductAndBackOrdersTurnedOn()
    {
        $bundleSku = 'bundle-product-out-of-stock';
        $bundleOptionSku = 'simple-out-of-stock';
        $qty = 3;
        $cart = $this->getCart();
        $defaultStockId = $this->defaultStockProvider->getId();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($bundleOptionSku, $defaultStockId);
        $stockItemConfigurationExtension = $stockItemConfiguration->getExtensionAttributes();
        $stockItemConfigurationExtension->setIsInStock(true);
        $stockItemConfiguration->setExtensionAttributes($stockItemConfigurationExtension);
        $this->saveStockItemConfiguration->execute($bundleOptionSku, $defaultStockId, $stockItemConfiguration);

        $bundleProduct = $this->productRepository->get($bundleSku);

        $cart->addProduct($bundleProduct, $this->getBuyRequest($bundleProduct, $qty));

        $this->cartRepository->save($cart);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/manage_stock 0
     */
    public function testPlaceOrderWithOutOfStockProductAndManageStockTurnedOff()
    {
        $bundleSku = 'bundle-product-out-of-stock';
        $qty = 3;
        $cart = $this->getCart();

        $bundleProduct = $this->productRepository->get($bundleSku);

        $cart->addProduct($bundleProduct, $this->getBuyRequest($bundleProduct, $qty));

        $this->cartRepository->save($cart);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @param ProductInterface $product
     * @param float $productQty
     *
     * @return DataObject
     */
    private function getBuyRequest(ProductInterface $product, float $productQty): DataObject
    {
        $bundleProductOptions = $product->getExtensionAttributes()->getBundleProductOptions();
        $bundleProductOption = reset($bundleProductOptions);
        $optionId = $bundleProductOption->getOptionId();

        $productLinks = $bundleProductOption->getProductLinks();
        $productLink = reset($productLinks);
        $productLinkId = $productLink->getOptionId();

        return new DataObject(
            [
                'product' => $product->getId(),
                'item' => $product->getId(),
                'bundle_option' => [$optionId => $productLinkId],
                'qty' => $productQty,
            ]
        );
    }

    /**
     * @return CartInterface
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
     *
     * @return void
     */
    private function deleteOrderById(int $orderId): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->orderManagement->cancel($orderId);
        $this->orderRepository->delete($this->orderRepository->get($orderId));
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->cleanupReservations->execute();
    }
}
