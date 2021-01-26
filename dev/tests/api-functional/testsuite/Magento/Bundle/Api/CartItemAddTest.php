<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Api;

use Magento\Catalog\Model\Product;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API test for add cart item with bundle product.
 */
class CartItemAddTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteAddCartItemV1';
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testAddItem(): void
    {
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class)->load(3);
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class)->load(
            'test_order_1',
            'reserved_order_id'
        );

        $itemQty = 1;
        $bundleProductOptions = $product->getExtensionAttributes()->getBundleProductOptions();
        $bundleOptionId = $bundleProductOptions[0]->getId();
        $optionSelections = $bundleProductOptions[0]->getProductLinks()[0]->getId();
        $buyRequest = [
            'bundle_option' => [$bundleOptionId => [$optionSelections]],
            'bundle_option_qty' => [$bundleOptionId => 1],
            'qty' => $itemQty,
            'original_qty' => $itemQty,
        ];

        $productSku = $product->getSku();
        $productId = $product->getId();
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $requestData = [
            "cartItem" => [
                "sku" => $productSku,
                "qty" => $itemQty,
                "quote_id" => $cartId,
                "product_option" => [
                    "extension_attributes" => [
                        "bundle_options" => [
                            [
                                "option_id" => (int)$bundleOptionId,
                                "option_qty" => $itemQty,
                                "option_selections" => [(int)$optionSelections],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($quote->hasProductId($productId));
        $this->assertEquals($buyRequest, $quote->getItemById($response['item_id'])->getBuyRequest()->getData());
    }
}
