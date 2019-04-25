<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Order;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

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
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

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
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    protected function setUp()
    {
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->orderManagement = Bootstrap::getObjectManager()->get(OrderManagementInterface::class);
        $this->dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->cleanupReservations->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     *
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderWithInStockProduct()
    {
        $sku = 'configurable';
        $qty = 3;

        $product = $this->getProductBySku($sku);
        $quote = $this->getQuote();

        $quote->addProduct($product, $this->getByRequest($product, $qty));
        $this->cartRepository->save($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        self::assertNotNull($orderId);

        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/set_product_configurable_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     *
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderWithOutOffStockProduct()
    {
        $sku = 'configurable';
        $qty = 6;

        $product = $this->getProductBySku($sku);
        $quote = $this->getQuote();

        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $this->getByRequest($product, $qty));
        $this->cartRepository->save($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        self::assertNull($orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/set_product_configurable_zero_qty.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoConfigFixture store_for_us_website_store cataloginventory/item_options/backorders 1
     *
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderWithZeroStockProductAndBackOrdersTurnedOn()
    {
        $sku = 'configurable';
        $qty = 8;

        $product = $this->getProductBySku($sku);
        $quote = $this->getQuote();

        $quote->addProduct($product, $this->getByRequest($product, $qty));
        $this->cartRepository->save($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        self::assertNotNull($orderId);

        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/set_product_configurable_zero_qty.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoConfigFixture current_store cataloginventory/item_options/manage_stock 0
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     *
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderWithZeroStockProductAndManageStockTurnedOff()
    {
        $sku = 'configurable';
        $qty = 6;

        $product = $this->getProductBySku($sku);
        $quote = $this->getQuote();

        $quote->addProduct($product, $this->getByRequest($product, $qty));
        $this->cartRepository->save($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        self::assertNotNull($orderId);

        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @return CartInterface
     */
    private function getQuote(): CartInterface
    {
        $storeCode = 'store_for_us_website';
        /** @var StoreInterface $store */
        $store = $this->storeRepository->get($storeCode);
        $this->storeManager->setCurrentStore($storeCode);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('reserved_order_id', 'test_order_1')
            ->create();
        /** @var CartInterface $cart */
        $cart = current($this->cartRepository->getList($searchCriteria)->getItems());
        $cart->setStoreId($store->getId());
        return $cart;
    }

    /**
     * @param ProductInterface $product
     * @param float $productQty
     *
     * @return DataObject
     */
    private function getByRequest(ProductInterface $product, float $productQty): DataObject
    {
        $configurableOptions = $product->getTypeInstance()->getConfigurableOptions($product);
        $option = current(current($configurableOptions));
        return $this->dataObjectFactory->create(
            [
                'product' => $option['product_id'],
                'super_attribute' => [key($configurableOptions) => $option['value_index']],
                'qty' => $productQty
            ]
        );
    }

    /**
     * @param string $sku
     * @return ProductInterface
     */
    private function getProductBySku(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku, false, 'store_for_us_website');
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

    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }
}
