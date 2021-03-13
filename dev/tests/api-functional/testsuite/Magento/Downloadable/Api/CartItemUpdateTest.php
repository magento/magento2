<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Api;

use Magento\Catalog\Model\Product;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API test for update cart item with downloadable product.
 */
class CartItemUpdateTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteUpdateCartItemV1';

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
     * @magentoApiDataFixture Magento/Downloadable/_files/quote_with_downloadable_product.php
     */
    public function testUpdateItem(): void
    {
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create(Product::class);
        $product->load($product->getIdBySku('downloadable-product'));
        // use ID of the first quote item
        $itemId = $quote->getAllItems()[0]->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items/' . $itemId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        // use ID of the first downloadable link
        $linkId = array_values($product->getDownloadableLinks())[0]->getId();

        $requestData = [
            'cartItem' => [
                'qty' => 2,
                'quote_id' => $cartId,
                'item_id' => $itemId,
                'sku' => 'downloadable-product',
                'product_option' => [
                    'extension_attributes' => [
                        'downloadable_option' => [
                            'downloadable_links' => [$linkId]
                        ]
                    ]
                ]
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotEmpty($response);
        $this->assertEquals('downloadable-product', $response['sku']);
        $this->assertEquals(2, $response['qty']);
        $this->assertCount(
            1,
            $response['product_option']['extension_attributes']['downloadable_option']['downloadable_links']
        );
        $this->assertContainsEquals(
            $linkId,
            $response['product_option']['extension_attributes']['downloadable_option']['downloadable_links']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/quote_with_downloadable_product.php
     */
    public function testUpdateItemWithInvalidLinkId(): void
    {
        $this->expectException(\Exception::class);

        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create(Product::class);
        $product->load($product->getIdBySku('downloadable-product'));
        // use ID of the first quote item
        $itemId = $quote->getAllItems()[0]->getId();
        $serviceInfo = [
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items/' . $itemId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
        ];

        $linkId = 9999;

        $requestData = [
            'cartItem' => [
                'qty' => 2,
                'quote_id' => $cartId,
                'item_id' => $itemId,
                'sku' => 'downloadable-product',
                'product_option' => [
                    'extension_attributes' => [
                        'downloadable_option' => [
                            'downloadable_links' => [$linkId]
                        ]
                    ]
                ]
            ],
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/quote_with_downloadable_product.php
     */
    public function testUpdateItemQty(): void
    {
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $product = $this->objectManager->create(Product::class);
        $product->load($product->getIdBySku('downloadable-product'));
        $cartId = $quote->getId();
        // use ID of the first quote item
        $itemId = $quote->getAllItems()[0]->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items/' . $itemId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        // use ID of the first downloadable link
        $linkId = array_values($product->getDownloadableLinks())[0]->getId();

        $requestData = [
            'cartItem' => [
                'qty' => 2,
                'quote_id' => $cartId,
                'item_id' => $itemId,
                'sku' => 'downloadable-product',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotEmpty($response);
        $this->assertEquals('downloadable-product', $response['sku']);
        $this->assertEquals(2, $response['qty']);
        $this->assertCount(
            1,
            $response['product_option']['extension_attributes']['downloadable_option']['downloadable_links']
        );
        $this->assertContainsEquals(
            $linkId,
            $response['product_option']['extension_attributes']['downloadable_option']['downloadable_links']
        );
    }
}
