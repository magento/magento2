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
 * API test for add cart item with downloadable product.
 */
class CartItemAddTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteAddCartItemV1';

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
     * @magentoApiDataFixture Magento/Quote/_files/empty_quote.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testAddItem(): void
    {
        /** @var  Product $product */
        $product = $this->objectManager->create(Product::class)->load(1);
        $productSku = $product->getSku();
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_id', 'reserved_order_id');
        $cartId = $quote->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
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
                'sku' => $productSku,
                'qty' => 1,
                'quote_id' => $cartId,
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
        $this->assertEquals(1, $response['qty']);
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
     * @magentoApiDataFixture Magento/Quote/_files/empty_quote.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testAddItemWithInvalidLinkId(): void
    {
        $this->expectException(\Exception::class);

        /** @var  Product $product */
        $product = $this->objectManager->create(Product::class)->load(1);
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_id', 'reserved_order_id');
        $cartId = $quote->getId();
        $productSku = $product->getSku();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $linkId = 9999;

        $requestData = [
            'cartItem' => [
                'sku' => $productSku,
                'qty' => 1,
                'quote_id' => $cartId,
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
}
