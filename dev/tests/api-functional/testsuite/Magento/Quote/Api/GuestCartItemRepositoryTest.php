<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class GuestCartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteGuestCartItemRepositoryV1';
    const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
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
        /** @var  \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load(2);
        $productSku = $product->getSku();
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
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
            "cartItem" => [
                "sku" => $productSku,
                "qty" => 7,
                "quote_id" => $cartId,
            ],
        ];
        $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($quote->hasProductId(2));
        $this->assertEquals(7, $quote->getItemByProduct($product)->getQty());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testRemoveItem()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
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
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertFalse($quote->hasProductId($productId));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testUpdateItem()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
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

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $requestData = [
                "cartItem" => [
                    "qty" => 5,
                    "quote_id" => $cartId,
                    "itemId" => $itemId,
                ],
            ];
        } else {
            $requestData = [
                "cartItem" => [
                    "qty" => 5,
                    "quote_id" => $cartId,
                ],
            ];
        }
        $this->_webApiCall($serviceInfo, $requestData);
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertTrue($quote->hasProductId(1));
        $item = $quote->getItemByProduct($product);
        $this->assertEquals(5, $item->getQty());
        $this->assertEquals($itemId, $item->getItemId());
    }
}
