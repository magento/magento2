<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_bundle_and_options.php
     */
    public function testGetAll()
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class)->load(
            'test_order_bundle',
            'reserved_order_id'
        );
        $quoteId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $quoteId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['cartId' => $quoteId]);
        $this->assertCount(1, $response);
        $response = $response[0];
        $bundleOption = $quote->getItemById($response['item_id'])->getBuyRequest()->getBundleOption();
        $bundleOptionQty = $quote->getItemById($response['item_id'])->getBuyRequest()->getBundleOptionQty();
        $actualOptions = $response['product_option']['extension_attributes']['bundle_options'];

        $this->assertEquals(array_keys($bundleOption), array_column($actualOptions, 'option_id'));
        $this->assertEquals($bundleOptionQty, array_column($actualOptions, 'option_qty', 'option_id'));
        foreach ($actualOptions as $option) {
            $id = $option['option_id'];
            $expectedSelections = is_array($bundleOption[$id]) ? $bundleOption[$id] : [$bundleOption[$id]];
            $this->assertEquals($expectedSelections, $option['option_selections']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testAddItem()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class)->load(3);
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class)->load(
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

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_bundle_and_options.php
     */
    public function testUpdate()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class)->load(
            'test_order_bundle',
            'reserved_order_id'
        );
        $cartId = $quote->getId();
        $cartItem = $quote->getAllVisibleItems()[0];
        $itemSku = $cartItem->getSku();
        $itemId = $cartItem->getId();

        $product = $cartItem->getProduct();
        /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $typeInstance->getOptionsCollection($product);
        $bundleOptions = [];
        /** @var $option \Magento\Bundle\Model\Option */
        foreach ($optionCollection as $option) {
            if (!$option->getRequired()) {
                continue;
            }
            $selectionsCollection = $typeInstance->getSelectionsCollection([$option->getId()], $product);
            $option = ['option_id' => $option->getId(), 'option_qty' => 1];
            $option['option_selections'] = [$selectionsCollection->getLastItem()->getSelectionId()];
            $bundleOptions[] = $option;
        }

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
        $requestData = [
            "cartItem" => [
                "sku" => $itemSku,
                "qty" => 2,
                "quote_id" => $cartId,
                "item_id" => $itemId,
                "product_option" => [
                    "extension_attributes" => [
                        "bundle_options" => $bundleOptions
                    ]
                ]
            ]
        ];
        $this->_webApiCall($serviceInfo, $requestData);

        $quoteUpdated = $this->objectManager->create(\Magento\Quote\Model\Quote::class)->load(
            'test_order_bundle',
            'reserved_order_id'
        );
        $cartItems = $quoteUpdated->getAllVisibleItems();
        $buyRequest = $cartItems[0]->getBuyRequest()->toArray();

        $this->assertCount(1, $cartItems);
        $this->assertEquals(count($buyRequest['bundle_option']), count($bundleOptions));
        foreach ($bundleOptions as $option) {
            $optionId = $option['option_id'];
            $optionQty = $option['option_qty'];
            $optionSelections = $option['option_selections'];
            $this->assertArrayHasKey($optionId, $buyRequest['bundle_option']);
            $this->assertEquals($optionQty, $buyRequest['bundle_option_qty'][$optionId]);
            $this->assertEquals($optionSelections, $buyRequest['bundle_option'][$optionId]);
        }
    }
}
