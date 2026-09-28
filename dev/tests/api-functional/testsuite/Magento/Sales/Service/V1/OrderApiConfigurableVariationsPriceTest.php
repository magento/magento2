<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Framework\Webapi\Rest\Request;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Checkout\Test\Fixture\SetGuestEmail;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod;
use Magento\Checkout\Test\Fixture\SetPaymentMethod;
use Magento\Checkout\Test\Fixture\PlaceOrder;

class OrderApiConfigurableVariationsPriceTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/orders';

    private const SERVICE_READ_NAME = 'salesOrderRepositoryV1';

    private const SERVICE_VERSION = 'V1';

    /**
     * Fixture storage manager for resolving test data.
     *
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * Set up fixture storage for retrieving test data.
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()
            ->get(DataFixtureStorageManager::class)
            ->getStorage();
    }

    #[
        DataFixture(
            AttributeFixture::class,
            [
                'frontend_input' => 'select',
                'options' => ['40', '42'],
                'is_configurable' => true,
                'is_global' => true,
            ],
            as: 'attribute'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100,
                'custom_attributes' => [
                    ['attribute_code' => '$attribute.attribute_code$', 'value' => '40'],
                ],
            ],
            as: 'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100,
                'custom_attributes' => [
                    ['attribute_code' => '$attribute.attribute_code$', 'value' => '42'],
                ],
            ],
            as: 'product2'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$attribute$'],
                '_links' => ['$product1$', '$product2$'],
                'custom_attributes' => [
                    ['attribute_code' => '$attribute.attribute_code$', 'value' => '40'],
                ],
            ],
            'configurable_product'
        ),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable_product.id$',
                'child_product_id' => '$product1.id$',
                'qty' => 1,
            ]
        ),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmail::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$cart.id$'], 'order')
    ]
    /**
     * Validates that simple products linked to a configurable parent in an order:
     * - Exist in the response
     * - Are linked via parent_item_id
     * - Carry expected pricing logic
     */
    public function testSimpleItemsAssignedToConfigurableHaveValidPrice(): void
    {
        $orderData = $this->callOrderApi((string) $this->fixtures->get('order')->getEntityId());

        $this->assertArrayHasKey('items', $orderData);
        $this->assertIsArray($orderData['items']);

        $configurableItems = [];
        $simpleItemsWithParent = [];
        $unlinkedSimples = [];

        foreach ($orderData['items'] as $item) {
            $type = $item['product_type'] ?? '';
            $parentId = $item['parent_item_id'] ?? null;

            if ($type === 'configurable') {
                $configurableItems[] = $item;
            } elseif ($type === 'simple') {
                if ($parentId) {
                    $simpleItemsWithParent[] = $item;
                } else {
                    $unlinkedSimples[] = $item;
                }
            }
        }

        $this->assertCount(1, $configurableItems, 'Expected 1 configurable parent item.');
        $this->assertCount(1, $simpleItemsWithParent, 'Expected 1 priced simple item linked to configurable.');

        foreach ($simpleItemsWithParent as $item) {
            $this->assertNotEmpty($item['sku'], 'Simple item must have SKU.');
            $price = (float) $item['price'];

            $this->assertTrue(
                true,
                sprintf('Simple item "%s" has price %s.', $item['sku'], $price)
            );

            if ($price > 0.0) {
                $this->assertGreaterThan(
                    0.0,
                    $price,
                    sprintf('Simple item "%s" should have price > 0.', $item['sku'])
                );
            }
        }

        foreach ($unlinkedSimples as $item) {
            $this->assertEquals(
                0.0,
                (float) $item['price'],
                'Unlinked simple item should have zero price.'
            );
        }

        $this->assertSimpleItemsHaveValidParent($configurableItems, $simpleItemsWithParent);
    }

    /**
     * Calls the REST and SOAP APIs to retrieve order data by order ID.
     *
     * @param string $orderId
     * @return array
     */
    private function callOrderApi(string $orderId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $orderId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['id' => $orderId]);
    }

    /**
     * Validates that simple items link correctly to one of the configurable parent items.
     *
     * @param array $configurableItems
     * @param array $simpleItems
     * @return void
     */
    private function assertSimpleItemsHaveValidParent(array $configurableItems, array $simpleItems): void
    {
        $configurableItemIds = array_column($configurableItems, 'item_id');

        foreach ($simpleItems as $item) {
            $this->assertContains(
                $item['parent_item_id'],
                $configurableItemIds,
                sprintf('Simple item "%s" must link to a configurable parent.', $item['item_id'] ?? 'N/A')
            );
        }
    }
}
