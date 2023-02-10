<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test for Magento\Quote\Api\GuestCartItemRepositoryInterface.
 */
class GuestCartItemRepositoryTest extends WebapiAbstract
{
    public const SERVICE_NAME = 'quoteGuestCartItemRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test quote items
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testGetList()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $output = [];
        /** @var  \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($quote->getAllItems() as $item) {
            //Set masked Cart ID
            $item->setQuoteId($cartId);
            $data = [
                'item_id' => $item->getItemId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'qty' => $item->getQty(),
                'product_type' => $item->getProductType(),
                'quote_id' => $item->getQuoteId()
            ];

            $output[] = $data;
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $this->assertEquals($output, $this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_without_options.php
     */
    public function testAddItem()
    {
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class)->load(2);
        $productSku = $product->getSku();
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = [
            'cartItem' => [
                'sku' => $productSku,
                'qty' => 7,
            ],
        ];

        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['cartItem']['quote_id'] = $cartId;
        }

        $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($quote->hasProductId(2));
        $this->assertEquals(7, $quote->getItemByProduct($product)->getQty());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testRemoveItem()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->objectManager->create(Product::class);
        $productId = $product->getIdBySku('simple_one');
        $product->load($productId);
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "itemId" => $itemId,
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertFalse($quote->hasProductId($productId));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     * @param array $stockData
     * @param string|null $errorMessage
     * @dataProvider updateItemDataProvider
     */
    public function testUpdateItem(array $stockData, string $errorMessage = null)
    {
        $this->updateStockData('simple_one', $stockData);
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->objectManager->create(Product::class);
        $productId = $product->getIdBySku('simple_one');
        $product->load($productId);
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData['cartItem']['qty'] = 5;
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['cartItem'] += [
                'quote_id' => $cartId,
                'itemId' => $itemId,
            ];
        }
        if ($errorMessage) {
            $this->expectExceptionMessage($errorMessage);
        }
        $this->_webApiCall($serviceInfo, $requestData);
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertTrue($quote->hasProductId(1));
        $item = $quote->getItemByProduct($product);
        $this->assertEquals(5, $item->getQty());
        $this->assertEquals($itemId, $item->getItemId());
    }

    /**
     * Verifies that store id for quote and quote item is being changed accordingly to the requested store code
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testUpdateItemWithChangingStoreId()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->objectManager->create(Product::class);
        $productId = $product->getIdBySku('simple');
        $product->load($productId);
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData['cartItem']['qty'] = 5;
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['cartItem'] += [
                'quote_id' => $cartId,
                'itemId' => $itemId,
            ];
        }
        $this->_webApiCall($serviceInfo, $requestData, null, 'fixture_second_store');
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertTrue($quote->hasProductId(1));
        $item = $quote->getItemByProduct($product);
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeId = $storeManager->getStore('fixture_second_store')
            ->getId();
        $this->assertEquals($storeId, $quote->getStoreId());
        $this->assertEquals($storeId, $item->getStoreId());
    }

    /**
     * @return array
     */
    public function updateItemDataProvider(): array
    {
        return [
            [
                []
            ],
            [
                [
                    'qty' => 0,
                    'is_in_stock' => 1,
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'use_config_backorders' => 0,
                    'backorders' => Stock::BACKORDERS_YES_NOTIFY,
                ]
            ],
            [
                [
                    'qty' => 0,
                    'is_in_stock' => 1,
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'use_config_backorders' => 0,
                    'backorders' => Stock::BACKORDERS_NO,
                ],
                'There are no source items with the in stock status'
            ],
            [
                [
                    'qty' => 2,
                    'is_in_stock' => 1,
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'use_config_backorders' => 0,
                    'backorders' => Stock::BACKORDERS_NO,
                ],
                'The requested qty is not available'
            ]
        ];
    }

    /**
     * Update product stock
     *
     * @param string $sku
     * @param array $stockData
     * @return void
     */
    private function updateStockData(string $sku, array $stockData): void
    {
        if ($stockData) {
            /** @var $stockRegistry StockRegistryInterface */
            $stockRegistry = $this->objectManager->create(StockRegistryInterface::class);
            $stockItem = $stockRegistry->getStockItemBySku($sku);
            $stockItem->addData($stockData);
            $stockRegistry->updateStockItemBySku($sku, $stockItem);
        }
    }
}
