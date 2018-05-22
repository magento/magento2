<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Order;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderOnDefaultStockTest extends TestCase
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
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var CollectionFactory
     */
    private $optionCollectionFactory;

    protected function setUp()
    {
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->orderManagement = Bootstrap::getObjectManager()->get(OrderManagementInterface::class);
        $this->eavConfig = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $this->optionCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/quote.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testPlaceOrderWithInStockProduct()
    {
        $sku = 'configurable';
        $qty = 90;

        $cart = $this->getCart();
        $product = $this->productRepository->get($sku);

        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'test_configurable');

        /** @var $options \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
        $options = $this->optionCollectionFactory->create();
        $option = $options->setAttributeFilter($attribute->getId())
            ->getFirstItem();

        $requestInfo = new \Magento\Framework\DataObject(
            [
                'product' => (int)$product->getId(),
                'selected_configurable_option' => 1,
                'qty' => $qty,
                'super_attribute' => [
                    $attribute->getId() => $option->getId()
                ]
            ]
        );
        $cart->addProduct($product, $requestInfo);

        $orderId = $this->cartManagement->placeOrder($cart->getId());

        self::assertNotNull($orderId);

        //cleanup
        $this->deleteOrderById((int)$orderId);
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
