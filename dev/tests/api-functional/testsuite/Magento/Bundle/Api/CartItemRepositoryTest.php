<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';
    const RESOURCE_PATH = '/V1/carts/';

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
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testAddItem()
    {
        /** @var  \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load(3);
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load(
            'test_order_item_with_items',
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
            'original_qty' => $itemQty
        ];

        $productSku = $product->getSku();
        $productId = $product->getId();
        /** @var \Magento\Quote\Model\Quote  $quote */

        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'items',
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
                "qty" => $itemQty,
                "quote_id" => $cartId,
                "product_option" => [
                    "extension_attributes" => [
                        "bundle_options" => [
                            [
                                "option_id" => (int)$bundleOptionId,
                                "option_qty" => $itemQty,
                                "option_selections" => [(int)$optionSelections]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($quote->hasProductId($productId));
        $this->assertEquals($buyRequest, $quote->getItemById($response['item_id'])->getBuyRequest()->getData());
    }
}
