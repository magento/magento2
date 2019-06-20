<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Test\Api;

use Magento\Framework\ObjectManager\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\StoreManagerInterface;

/**
 * {@inheritDoc}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/908874/scenarios/3042336
 */
class ExportStockIndexDataTestStockTestWebsiteReservationsTest extends WebapiAbstract
{
    const API_PATH = '/V1/inventory/dump-stock-index-data';
    const SERVICE_NAME = 'inventoryExportStockApiExportStockIndexDataV1';
    const EXPORT_PRODUCT_COUNT = 7;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ExportStockIndexDataOnCustomStockTest
     */
    private $parent;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->parent = $this->objectManager->create(ExportStockIndexDataOnCustomStockTest::class);
    }

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            ['website', 'us_website', self::EXPORT_PRODUCT_COUNT]
        ];
    }

    /**
     * Check possibility of exporting salable quantity stock data for different types of products on default stock.
     *
     * @param string $type
     * @param string $code
     * @param int $expectedResult
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider executeDataProvider
     * @magentoDbIsolation disabled
     */
    public function testExportStockDataOnCustomStock(string $type, string $code, int $expectedResult): void
    {
        $products = [
            'downloadable-product',
            'virtual-product',
            'grouped-product',
            'simple_10',
            'simple_30',
            'simple_40',
            'simple_20'
        ];
        $this->parent->assignProductToAdditionalWebsite($products, 'us_website');
        $this->parent->assignWebsiteToStock(20, 'us_website');
        $this->parent->assignProductToSource($products, 'us-1');
        $orderIds = $this->placeOrder($products);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/' . $type . '/' . $code,
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];

        $res = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => $code, 'salesChannelType' => $type]);

        self::assertEquals($expectedResult, count($res));
        $this->deleteOrderById($orderIds);
    }

    /**
     * Place order.
     *
     * @param array $skus
     * @return array
     */
    private function placeOrder(array $skus)
    {
        $orderIds = [];
        foreach ($skus as $key => $sku) {
            $quoteItemQty = 2;
            $cart = $this->getCart($key);
            $product = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class)->get($sku);
            $cartItem = $this->getCartItem($product, $quoteItemQty, (int)$cart->getId());
            $cart->addItem($cartItem);
            Bootstrap::getObjectManager()->get(CartRepositoryInterface::class)->save($cart);
            $orderIds[] = Bootstrap::getObjectManager()->get(CartManagementInterface::class)->placeOrder($cart->getId());
        }
        return $orderIds;
    }

    /**
     * Return cart.
     *
     * @return CartInterface
     */
    private function getCart($key): CartInterface
    {
        $this->createQuote($key);
        $searchCriteria = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class)
            ->addFilter('reserved_order_id', 'test_order_' . $key)
            ->create();
        /** @var CartInterface $cart */
        $cart = current(Bootstrap::getObjectManager()->get(CartRepositoryInterface::class)
            ->getList($searchCriteria)
            ->getItems());
        $store = Bootstrap::getObjectManager()->get(StoreRepository::class)->get('store_for_us_website');
        $cart->setReservedOrderId('test_order_' . $key);
        Bootstrap::getObjectManager()->get(StoreManagerInterface::class)->setCurrentStore('store_for_us_website');
        $cart->setStoreId($store->getId());

        return $cart;
    }

    /**
     * Create quote.
     *
     * @param int $key
     */
    private function createQuote(int $key)
    {
        $cartId = Bootstrap::getObjectManager()->get(CartManagementInterface::class)->createEmptyCart();
        /** @var CartInterface $cart */
        $cart = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class)->get($cartId);
        $cart->setCustomerEmail('admin@example.com');
        $cart->setCustomerIsGuest(true);
        /** @var AddressInterface $address */
        $address = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class)->create(
            [
                'data' => [
                    AddressInterface::KEY_COUNTRY_ID => 'US',
                    AddressInterface::KEY_REGION_ID => 15,
                    AddressInterface::KEY_LASTNAME => 'Doe',
                    AddressInterface::KEY_FIRSTNAME => 'John',
                    AddressInterface::KEY_STREET => 'example street',
                    AddressInterface::KEY_EMAIL => 'customer@example.com',
                    AddressInterface::KEY_CITY => 'example city',
                    AddressInterface::KEY_TELEPHONE => '000 0000',
                    AddressInterface::KEY_POSTCODE => 12345
                ]
            ]
        );
        $store = Bootstrap::getObjectManager()->get(StoreRepository::class)->get('store_for_us_website');
        Bootstrap::getObjectManager()->get(StoreManagerInterface::class)->setCurrentStore('store_for_us_website');
        $cart->setStoreId($store->getId());
        $cart->setReservedOrderId('test_order_'.$key);
        $cart->setBillingAddress($address);
        $cart->setShippingAddress($address);
        $cart->getPayment()->setMethod('checkmo');
        $cart->getShippingAddress()->setShippingMethod('flatrate_flatrate');
        $cart->getShippingAddress()->setCollectShippingRates(true);
        $cart->getShippingAddress()->collectShippingRates();
        Bootstrap::getObjectManager()->get(CartRepositoryInterface::class)->save($cart);
    }
    /**
     * Delete orders.
     *
     * @param array $orderIds
     */
    private function deleteOrderById(array $orderIds)
    {
        Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class)->unregister('isSecureArea');
        Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class)->register('isSecureArea', true);
        foreach ($orderIds as $orderId) {
            Bootstrap::getObjectManager()->get(OrderManagementInterface::class)->cancel($orderId);
            Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class)->delete(Bootstrap::getObjectManager()
                ->get(OrderRepositoryInterface::class)
                ->get($orderId));
        }
        Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class)->unregister('isSecureArea');
        Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class)->register('isSecureArea', false);
    }

    /**
     * Return quote item.
     *
     * @param ProductInterface $product
     * @param float $quoteItemQty
     * @param int $cartId
     * @return CartItemInterface
     */
    private function getCartItem(ProductInterface $product, float $quoteItemQty, int $cartId): CartItemInterface
    {
        /** @var CartItemInterface $cartItem */
        $cartItem =
            Bootstrap::getObjectManager()->get(CartItemInterfaceFactory::class)->create(
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
}
