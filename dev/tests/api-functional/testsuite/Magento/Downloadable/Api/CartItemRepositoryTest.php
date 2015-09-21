<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testAddItem()
    {
        /** @var  \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load(1);
        $productSku = $product->getSku();
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
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
            'cartItem' => [
                'sku' => $productSku,
                'qty' => 1,
                'quote_id' => $cartId,
                'product_option' => [
                    'extension_attributes' => [
                        'downloadable_option' => [
                            'downloadable_links' => [0]
                        ]
                    ]
                ]
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotEmpty($response);
        $this->assertEquals('downloadable-product', $response['sku']);
        $this->assertEquals(1, $response['qty']);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/quote_with_downloadable_product.php
     */
    public function testUpdateItem()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($product->getIdBySku('downloadable-product'));
        $itemId = $quote->getAllItems()[0]->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = [
            'cartItem' => [
                'qty' => 2,
                'quote_id' => $cartId,
                'item_id' => $itemId,
                'sku' => 'downloadable-product',
                'product_option' => [
                    'extension_attributes' => [
                        'downloadable_option' => [
                            'downloadable_links' => [0]
                        ]
                    ]
                ]
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotEmpty($response);
        $this->assertEquals('downloadable-product', $response['sku']);
        $this->assertEquals(2, $response['qty']);
    }
}
