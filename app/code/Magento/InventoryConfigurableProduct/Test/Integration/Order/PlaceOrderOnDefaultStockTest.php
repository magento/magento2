<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Order;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    protected function setUp()
    {
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->orderManagement = Bootstrap::getObjectManager()->get(OrderManagementInterface::class);
        $this->dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);
        $this->cleanupReservations->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     */
    public function testPlaceOrderWithInStockProduct()
    {
        $sku = 'configurable_in_stock';
        $qty = 4;

        $product = $this->productRepository->get($sku);
        $quote = $this->getQuote();

        $quote->addProduct($product, $this->getByRequest($product, $qty));
        $this->cartRepository->save($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoConfigFixture current_store cataloginventory/item_options/backorders 1
     */
    public function testPlaceOrderWithOutOffStockProductAndBackOrdersTurnedOn()
    {
        $sku = 'configurable_out_of_stock';
        $qty = 8;

        $product = $this->productRepository->get($sku);
        $quote = $this->getQuote();
        self::expectExceptionMessage('Product that you are trying to add is not available.');
        $quote->addProduct($product, $this->getByRequest($product, $qty));
        $this->cartRepository->save($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     */
    public function testPlaceOrderWithOutOffStockProduct()
    {
        $sku = 'configurable_out_of_stock';
        $qty = 8;

        $quote = $this->getQuote();
        $product = $this->productRepository->get($sku);

        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $this->getByRequest($product, $qty));
        $this->cartRepository->save($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        self::assertNull($orderId);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/disable_config_use_manage_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     */
    public function testPlaceOrderWithOutOffStockProductAndManageStockTurnedOff()
    {
        $sku = 'configurable_out_of_stock';
        $qty = 8;

        $product = $this->productRepository->get($sku);
        $quote = $this->getQuote();
        $quote->addProduct($product, $this->getByRequest($product, $qty));
        $this->cartRepository->save($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
    }

    /**
     * @return CartInterface
     */
    private function getQuote(): CartInterface
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
