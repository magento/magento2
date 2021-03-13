<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Catalog\Model\CustomOptions\CustomOptionProcessor;
use Magento\Catalog\Model\Product;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API test for cart item repository
 */
class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';
    const RESOURCE_PATH = '/V1/carts/';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_and_custom_options_saved.php
     */
    public function testGetList()
    {
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items_and_custom_options', 'reserved_order_id');
        $cartId = $quote->getId();
        $output = [];
        $customOptionProcessor = $this->objectManager->get(CustomOptionProcessor::class);

        /** @var  CartItemInterface $item */
        foreach ($quote->getAllItems() as $item) {
            $customOptionProcessor->processOptions($item);
            $data = [
                'item_id' => (int)$item->getItemId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => (float)$item->getPrice(),
                'qty' => (float)$item->getQty(),
                'product_type' => $item->getProductType(),
                'quote_id' => $item->getQuoteId(),
            ];

            if ($item->getProductOption() !== null) {
                $customOptions = $item->getProductOption()->getExtensionAttributes()->getCustomOptions();
                foreach ($customOptions as $option) {
                    $data['product_option']['extension_attributes']['custom_options'][] = $option->getData();
                }
            }

            $output[] = $data;
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_GET,
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testRemoveItem()
    {
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create(Product::class);
        $productId = $product->getIdBySku('simple_one');
        $product->load($productId);
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items/' . $itemId,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
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
}
