<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class for testing adding and deleting items flow.
 */
class GuestCartAddingItemsTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';
    private const SERVICE_NAME = 'quoteGuestCartManagementV1';
    private const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;
    
    /**
     * @var ProductResource|mixed
     */
    private mixed $productResource;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResource = $this->objectManager->get(ProductResource::class);
    }

    /**
     * Test add to product with custom option and test with updating custom options.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_options.php
     * @return void
     */
    public function testAddtoCartWithCustomOptionsForCreatingQuoteFromEmptyCart()
    {
        $this->_markTestAsRestOnly();

        $productId = $this->productResource->getIdBySku('simple_with_custom_options');
        $product = $this->objectManager->create(Product::class)->load($productId);
        $customOptionCollection = $this->objectManager->get(Option::class)
            ->getProductOptionCollection($product);
        $customOptions = [];
        foreach ($customOptionCollection as $option) {
            $customOptions [] = [
                'option_id' => $option->getId(),
                'option_value' => $option->getType() !== 'field' ? 1 : 'test'
            ];
        }

        // Creating empty cart
        $serviceInfoForCreatingEmptyCart = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];
        $quoteId = $this->_webApiCall($serviceInfoForCreatingEmptyCart);

        // Adding item to the cart
        $serviceInfoForAddingProduct = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $quoteId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'simple_with_custom_options',
                'qty' => 1,
                'product_option' => [
                    'extension_attributes' => [
                        'custom_options' => $customOptions
                    ]
                ]
            ]
        ];
        $item = $this->_webApiCall($serviceInfoForAddingProduct, $requestData);
        $this->assertNotEmpty($item);
        foreach ($customOptionCollection as $option) {
            $customOptions [] = [
                'option_id' => $option->getId(),
                'option_value' => $option->getType() != 'field' ? 2 : 'test2'
            ];
        }
        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'simple_with_custom_options',
                'qty' => 1,
                'product_option' => [
                    'extension_attributes' => [
                        'custom_options' => $customOptions
                    ]
                ]
            ]
        ];

        // Update the item for the cart
        $serviceInfoForUpdateProduct = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $quoteId . '/items/' . $item['item_id'],
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];

        $item = $this->_webApiCall($serviceInfoForUpdateProduct, $requestData);
        $this->assertNotEmpty($item);
    }

    /**
     * Test price for cart after deleting and adding product to.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @return void
     */
    public function testPriceForCreatingQuoteFromEmptyCart()
    {
        // Creating empty cart
        $serviceInfoForCreatingEmptyCart = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateEmptyCart',
            ],
        ];
        $quoteId = $this->_webApiCall($serviceInfoForCreatingEmptyCart);

        // Adding item to the cart
        $serviceInfoForAddingProduct = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $quoteId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => GuestCartItemRepositoryTest::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => GuestCartItemRepositoryTest::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'simple',
                'qty' => 1
            ]
        ];
        $item = $this->_webApiCall($serviceInfoForAddingProduct, $requestData);
        $this->assertNotEmpty($item);

        // Delete the item for the cart
        $serviceInfoForDeleteProduct = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $quoteId . '/items/' . $item['item_id'],
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => GuestCartItemRepositoryTest::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => GuestCartItemRepositoryTest::SERVICE_NAME . 'deleteById',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfoForDeleteProduct, ['cartId' => $quoteId, 'itemId' => $item['item_id']])
            : $this->_webApiCall($serviceInfoForDeleteProduct);
        $this->assertTrue($response);

        // Add one more item and check price for this item
        $serviceInfoForAddingProduct = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $quoteId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => GuestCartItemRepositoryTest::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => GuestCartItemRepositoryTest::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'simple',
                'qty' => 1
            ]
        ];
        $item = $this->_webApiCall($serviceInfoForAddingProduct, $requestData);
        $this->assertNotEmpty($item);
        $this->assertEquals($item['price'], 10);

        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load($quoteId);
        $quote->delete();
    }
}
