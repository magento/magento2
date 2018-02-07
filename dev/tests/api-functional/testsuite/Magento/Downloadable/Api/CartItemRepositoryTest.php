<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Quote/_files/empty_quote.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testAddItem()
    {
        /** @var  \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load(1);
        $productSku = $product->getSku();
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('reserved_order_id', 'reserved_order_id');
        $cartId = $quote->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
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
        $this->assertContains(
            $linkId,
            $response['product_option']['extension_attributes']['downloadable_option']['downloadable_links']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Quote/_files/empty_quote.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     */
    public function testAddItemWithInvalidLinkId()
    {
        /** @var  \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load(1);
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('reserved_order_id', 'reserved_order_id');
        $cartId = $quote->getId();
        $productSku = $product->getSku();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
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
        // use ID of the first quote item
        $itemId = $quote->getAllItems()[0]->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
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
        $this->assertContains(
            $linkId,
            $response['product_option']['extension_attributes']['downloadable_option']['downloadable_links']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/quote_with_downloadable_product.php
     * @expectedException \Exception
     */
    public function testUpdateItemWithInvalidLinkId()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($product->getIdBySku('downloadable-product'));
        // use ID of the first quote item
        $itemId = $quote->getAllItems()[0]->getId();
        $serviceInfo = [
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
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
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($product->getIdBySku('downloadable-product'));
        // use ID of the first downloadable link
        $linkId = array_values($product->getDownloadableLinks())[0]->getId();

        /** @var  \Magento\Quote\Api\Data\CartItemInterface $item */
        $item = $quote->getAllItems()[0];
        $expectedResult = [[
            'item_id' => $item->getItemId(),
            'sku' => $item->getSku(),
            'name' => $item->getName(),
            'price' => $item->getPrice(),
            'qty' => $item->getQty(),
            'product_type' => $item->getProductType(),
            'quote_id' => $item->getQuoteId(),
            'product_option' => [
                'extension_attributes' => [
                    'downloadable_option' => [
                        'downloadable_links' => [$linkId]
                    ]
                ]
            ]
        ]];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $this->assertEquals($expectedResult, $this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/quote_with_downloadable_product.php
     */
    public function testUpdateItemQty()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($product->getIdBySku('downloadable-product'));
        $cartId = $quote->getId();
        // use ID of the first quote item
        $itemId = $quote->getAllItems()[0]->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
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
        $this->assertContains(
            $linkId,
            $response['product_option']['extension_attributes']['downloadable_option']['downloadable_links']
        );
    }
}
