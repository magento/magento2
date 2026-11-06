<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Api;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Backordered quantity of configurable product order items placed through the guest cart API
 */
class OrderItemBackorderedQtyTest extends WebapiAbstract
{
    /**
     * The backordered quantity of a variant must be stored on the configurable order item as well
     */
    #[
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(
            ProductFixture::class,
            [
                'extension_attributes' => [
                    'stock_item' => [
                        'qty' => 1,
                        'is_in_stock' => true,
                        'use_config_backorders' => false,
                        'backorders' => 1,
                    ],
                ],
            ],
            'variant'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$variant$']],
            'configurable'
        ),
    ]
    public function testConfigurableOrderItemKeepsBackorderedQtyOfVariant(): void
    {
        $this->_markTestAsRestOnly();
        $fixtures = DataFixtureStorageManager::getStorage();
        $attribute = $fixtures->get('attribute');
        $variant = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class)
            ->get($fixtures->get('variant')->getSku(), false, null, true);

        $cartId = $this->call('/V1/guest-carts', Request::HTTP_METHOD_POST);
        $this->call(
            "/V1/guest-carts/$cartId/items",
            Request::HTTP_METHOD_POST,
            [
                'cartItem' => [
                    'sku' => $fixtures->get('configurable')->getSku(),
                    'qty' => 3,
                    'quote_id' => $cartId,
                    'product_option' => [
                        'extension_attributes' => [
                            'configurable_item_options' => [
                                [
                                    'option_id' => $attribute->getAttributeId(),
                                    'option_value' => $variant->getData($attribute->getAttributeCode()),
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $address = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'guest@example.com',
            'street' => ['Main Street 1'],
            'city' => 'Austin',
            'region_id' => 57,
            'postcode' => '78701',
            'country_id' => 'US',
            'telephone' => '5555555555',
        ];
        $this->call(
            "/V1/guest-carts/$cartId/shipping-information",
            Request::HTTP_METHOD_POST,
            [
                'addressInformation' => [
                    'shipping_address' => $address,
                    'billing_address' => $address,
                    'shipping_carrier_code' => 'flatrate',
                    'shipping_method_code' => 'flatrate',
                ],
            ]
        );
        $orderId = $this->call(
            "/V1/guest-carts/$cartId/order",
            Request::HTTP_METHOD_PUT,
            ['paymentMethod' => ['method' => 'checkmo']]
        );

        $order = $this->call("/V1/orders/$orderId", Request::HTTP_METHOD_GET);
        $backorderedQty = [];
        foreach ($order['items'] as $item) {
            $backorderedQty[$item['product_type']] = (float)($item['qty_backordered'] ?? 0);
        }
        $this->assertEquals(['configurable' => 2.0, 'simple' => 2.0], $backorderedQty);
    }

    /**
     * Call a REST endpoint
     *
     * @param string $resourcePath
     * @param string $httpMethod
     * @param array $arguments
     * @return mixed
     */
    private function call(string $resourcePath, string $httpMethod, array $arguments = [])
    {
        return $this->_webApiCall(
            ['rest' => ['resourcePath' => $resourcePath, 'httpMethod' => $httpMethod]],
            $arguments
        );
    }
}
