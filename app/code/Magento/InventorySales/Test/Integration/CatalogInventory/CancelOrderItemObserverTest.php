<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\CatalogInventory;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelOrderItemObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var CleanupReservationsInterface
     */
    private $cleanupReservations;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getSalableQuantity;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
        $this->dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->orderManagement = Bootstrap::getObjectManager()->get(OrderManagementInterface::class);
        $this->getSalableQuantity = Bootstrap::getObjectManager()->get(GetProductSalableQtyInterface::class);
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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDbIsolation disabled
     */
    public function testPlaceReservationWhenOrderItemIsCanceled()
    {
        // Place an order of configurable product with two simple products assigned to not default source and qty > 1
        $stockId = 20;
        $product = $this->productRepository->get('configurable');
        $quote = $this->getCartByStockId($stockId);
        $quote->addProduct($product, $this->getByRequest($product, 2));
        $this->cartRepository->save($quote);
        $salableQtyBeforeOrder = $this->getSalableQuantity->execute('simple_10', $stockId);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        // Cancel order
        $this->deleteOrderById((int)$orderId);
        $salableQtyAfterOrderCancel = $this->getSalableQuantity->execute('simple_10', $stockId);

        // Expected result after cancel: reservation is compensated, salable qty is the same
        $this->assertEquals($salableQtyBeforeOrder, $salableQtyAfterOrderCancel);
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

    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }
}
