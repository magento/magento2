<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Catalog\Model\Product;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API test for update cart item
 */
class CartItemUpdateTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteUpdateCartItemV1';
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testUpdateItem(): void
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
                'resourcePath' => self::RESOURCE_PATH .  $cartId . '/items/' . $itemId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
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
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertTrue($quote->hasProductId(1));
        $item = $quote->getItemByProduct($product);
        $this->assertEquals(5, $item->getQty());
        $this->assertEquals($itemId, $item->getItemId());
    }
}
