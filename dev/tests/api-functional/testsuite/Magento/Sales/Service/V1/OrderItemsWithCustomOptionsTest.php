<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test retrieving order items with all custom option types via REST API
 */
class OrderItemsWithCustomOptionsTest extends WebapiAbstract
{
    private const RESOURCE_PATH_ORDER_ITEMS = '/V1/orders/items';
    private const RESOURCE_PATH_GUEST_CART = '/V1/guest-carts';
    private const RESOURCE_PATH_ORDERS = '/V1/orders';

    /**
     * @var ProductResource
     */
    private ProductResource $productResource;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * Set up test dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();

        $objectManager = Bootstrap::getObjectManager();
        $this->productResource = $objectManager->get(ProductResource::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test creating order with products having all custom option types and retrieving via REST API
     *
     * @return void
     */
    #[DataFixture(
        ProductFixture::class,
        [
            'sku' => 'product-all-options-simple',
            'name' => 'Product All Options Simple',
            'price' => 100,
            'weight' => 1,
            'options' => [
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    'title' => 'Text Field',
                    'is_require' => true,
                    'price' => 5,
                    'price_type' => 'fixed',
                    'max_characters' => 100,
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    'title' => 'Text Area',
                    'is_require' => true,
                    'price' => 10,
                    'price_type' => 'fixed',
                    'max_characters' => 500,
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    'title' => 'Dropdown',
                    'is_require' => true,
                    'values' => [
                        ['title' => 'Dropdown Option 1', 'price' => 15, 'price_type' => 'fixed'],
                        ['title' => 'Dropdown Option 2', 'price' => 20, 'price_type' => 'fixed'],
                    ],
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    'title' => 'Radio Button',
                    'is_require' => true,
                    'values' => [
                        ['title' => 'Radio Option 1', 'price' => 25, 'price_type' => 'fixed'],
                        ['title' => 'Radio Option 2', 'price' => 30, 'price_type' => 'fixed'],
                    ],
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    'title' => 'Checkbox',
                    'is_require' => true,
                    'values' => [
                        ['title' => 'Checkbox Option 1', 'price' => 35, 'price_type' => 'fixed'],
                        ['title' => 'Checkbox Option 2', 'price' => 40, 'price_type' => 'fixed'],
                    ],
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    'title' => 'Multiple Select',
                    'is_require' => true,
                    'values' => [
                        ['title' => 'Multiple Option 1', 'price' => 45, 'price_type' => 'fixed'],
                        ['title' => 'Multiple Option 2', 'price' => 50, 'price_type' => 'fixed'],
                        ['title' => 'Multiple Option 3', 'price' => 55, 'price_type' => 'fixed'],
                    ],
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    'title' => 'Date',
                    'is_require' => true,
                    'price' => 60,
                    'price_type' => 'fixed',
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    'title' => 'Date and Time',
                    'is_require' => true,
                    'price' => 65,
                    'price_type' => 'fixed',
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    'title' => 'Time',
                    'is_require' => true,
                    'price' => 70,
                    'price_type' => 'fixed',
                ],
            ],
        ],
        as: 'product1'
    )]
    #[DataFixture(
        ProductFixture::class,
        [
            'sku' => 'product-all-options-virtual',
            'name' => 'Product All Options Virtual',
            'type_id' => 'virtual',
            'price' => 150,
            'options' => [
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    'title' => 'Text Field 2',
                    'is_require' => true,
                    'price' => 8,
                    'price_type' => 'fixed',
                    'max_characters' => 150,
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    'title' => 'Text Area 2',
                    'is_require' => true,
                    'price' => 12,
                    'price_type' => 'fixed',
                    'max_characters' => 600,
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    'title' => 'Dropdown 2',
                    'is_require' => true,
                    'values' => [
                        ['title' => 'Dropdown 2 Option 1', 'price' => 18, 'price_type' => 'fixed'],
                        ['title' => 'Dropdown 2 Option 2', 'price' => 22, 'price_type' => 'fixed'],
                    ],
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    'title' => 'Radio Button 2',
                    'is_require' => true,
                    'values' => [
                        ['title' => 'Radio 2 Option 1', 'price' => 28, 'price_type' => 'fixed'],
                    ],
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    'title' => 'Checkbox 2',
                    'is_require' => true,
                    'values' => [
                        ['title' => 'Checkbox 2 Option 1', 'price' => 38, 'price_type' => 'fixed'],
                        ['title' => 'Checkbox 2 Option 2', 'price' => 42, 'price_type' => 'fixed'],
                    ],
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    'title' => 'Multiple Select 2',
                    'is_require' => true,
                    'values' => [
                        ['title' => 'Multiple 2 Option 1', 'price' => 48, 'price_type' => 'fixed'],
                        ['title' => 'Multiple 2 Option 2', 'price' => 52, 'price_type' => 'fixed'],
                    ],
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    'title' => 'Date 2',
                    'is_require' => true,
                    'price' => 62,
                    'price_type' => 'fixed',
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    'title' => 'Date and Time 2',
                    'is_require' => true,
                    'price' => 68,
                    'price_type' => 'fixed',
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    'title' => 'Time 2',
                    'is_require' => true,
                    'price' => 72,
                    'price_type' => 'fixed',
                ],
            ],
        ],
        as: 'product2'
    )]
    #[DataFixture(Indexer::class, as: 'indexer')]
    public function testCreateTwoProductsWithAllCustomOptionsAndRetrieveOrderItems(): void
    {
        /** @var ProductInterface $product1 */
        $product1 = $this->fixtures->get('product1');
        /** @var ProductInterface $product2 */
        $product2 = $this->fixtures->get('product2');

        $cartId = $this->createGuestCart();

        $customOptions1 = $this->prepareCustomOptionsForProductBySku($product1->getSku());
        $this->addProductToCart($cartId, $product1, $customOptions1);

        $customOptions2 = $this->prepareCustomOptionsForProductBySku($product2->getSku());
        $this->addProductToCart($cartId, $product2, $customOptions2);

        $this->setShippingInformation($cartId);
        $orderId = $this->placeOrder($cartId);
        self::assertNotEmpty($orderId, 'Order should be created');

        $order = $this->getOrder((int)$orderId);
        self::assertArrayHasKey('entity_id', $order);
        $orderEntityId = (int)$order['entity_id'];

        $orderItems = $this->getOrderItemsByOrderId($orderEntityId);

        self::assertIsArray($orderItems);
        self::assertArrayHasKey('items', $orderItems);
        self::assertArrayHasKey('search_criteria', $orderItems);
        self::assertArrayHasKey('total_count', $orderItems);
        self::assertEquals(2, $orderItems['total_count']);
        self::assertCount(2, $orderItems['items']);

        $searchCriteria = $orderItems['search_criteria'];
        self::assertArrayHasKey('filter_groups', $searchCriteria);
        $filters = $searchCriteria['filter_groups'][0]['filters'];
        self::assertEquals('order_id', $filters[0]['field']);
        self::assertEquals($orderEntityId, $filters[0]['value']);
        self::assertEquals('eq', $filters[0]['condition_type']);

        foreach ($orderItems['items'] as $item) {
            $this->assertOrderItemStructure($item);
            $this->assertOrderItemCustomOptions($item);
        }
    }

    /**
     * Assert order item has the required structure and fields
     *
     * @param array $item Order item data
     * @return void
     */
    private function assertOrderItemStructure(array $item): void
    {
        self::assertArrayHasKey('item_id', $item);
        self::assertArrayHasKey('order_id', $item);
        self::assertArrayHasKey('product_id', $item);
        self::assertArrayHasKey('sku', $item);
        self::assertArrayHasKey('name', $item);
        self::assertArrayHasKey('price', $item);
        self::assertArrayHasKey('qty_ordered', $item);
        self::assertArrayHasKey('product_type', $item);
        self::assertArrayHasKey('created_at', $item);
        self::assertArrayHasKey('updated_at', $item);
        self::assertArrayHasKey('product_option', $item);
        self::assertArrayHasKey('extension_attributes', $item['product_option']);
        self::assertArrayHasKey('custom_options', $item['product_option']['extension_attributes']);
    }

    /**
     * Assert order item custom options are valid
     *
     * @param array $item Order item data containing custom options
     * @return void
     */
    private function assertOrderItemCustomOptions(array $item): void
    {
        $customOptions = $item['product_option']['extension_attributes']['custom_options'];
        self::assertIsArray($customOptions);
        self::assertCount(9, $customOptions, sprintf(
            'Expected exactly 9 custom options for item "%s", but found %d',
            $item['sku'],
            count($customOptions)
        ));

        foreach ($customOptions as $index => $option) {
            $this->assertCustomOptionStructure($option, $index, $item['sku']);
            $this->validateCustomOptionValue(
                $option['option_value'],
                $index,
                $item['sku']
            );
        }
    }

    /**
     * Assert custom option has required structure and valid data
     *
     * @param array $option Custom option data
     * @param int $index Option index in the array
     * @param string $sku Product SKU for error messages
     * @return void
     */
    private function assertCustomOptionStructure(array $option, int $index, string $sku): void
    {
        self::assertArrayHasKey(
            'option_id',
            $option,
            sprintf('Custom option at index %d for item "%s" is missing option_id', $index, $sku)
        );
        self::assertArrayHasKey(
            'option_value',
            $option,
            sprintf('Custom option at index %d for item "%s" is missing option_value', $index, $sku)
        );
        self::assertNotEmpty(
            $option['option_id'],
            sprintf('Custom option at index %d for item "%s" has empty option_id', $index, $sku)
        );
        self::assertIsString(
            $option['option_id'],
            sprintf('Custom option at index %d for item "%s" has non-string option_id', $index, $sku)
        );
        self::assertMatchesRegularExpression(
            '/^\d+$/',
            $option['option_id'],
            sprintf('Custom option at index %d for item "%s" has non-numeric option_id', $index, $sku)
        );

        $optionValue = $option['option_value'];
        if (is_array($optionValue)) {
            self::assertNotEmpty(
                $optionValue,
                sprintf('Custom option at index %d for item "%s" has empty option_value', $index, $sku)
            );
        } else {
            self::assertNotEmpty(
                $optionValue,
                sprintf('Custom option at index %d for item "%s" has empty option_value', $index, $sku)
            );
        }
    }

    /**
     * Create a guest cart via REST API
     *
     * @return string Cart ID
     */
    private function createGuestCart(): string
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_GUEST_CART,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        return (string)$this->_webApiCall($serviceInfo);
    }

    /**
     * Prepare custom options data for a product by SKU
     *
     * This method loads the product and prepares custom option values for all option types
     * including field, area, drop_down, radio, checkbox, multiple, date, date_time, and time
     *
     * @param string $sku Product SKU
     * @return array Array of custom options with option_id and option_value
     */
    private function prepareCustomOptionsForProductBySku(string $sku): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $productId = $this->productResource->getIdBySku($sku);
        $product = $objectManager->create(Product::class)->load($productId);

        $customOptionCollection = $objectManager->get(Option::class)
            ->getProductOptionCollection($product);

        $customOptions = [];
        foreach ($customOptionCollection as $option) {
            $optionType = $option->getType();

            $optionData = match ($optionType) {
                'field' => 'Test field value',
                'area' => 'Test textarea value',
                'drop_down', 'radio' => $this->getFirstOptionValue($option),
                'checkbox', 'multiple' => $this->getAllOptionValues($option),
                'date' => '2024-12-25 00:00:00',
                'date_time' => '2024-12-25 14:30:00',
                'time' => '2024-01-01 10:45:00',
                default => null
            };

            if ($optionData !== null) {
                $customOptions[] = [
                    'option_id' => $option->getId(),
                    'option_value' => $optionData
                ];
            }
        }

        return $customOptions;
    }

    /**
     * Get the first option value ID from a custom option
     *
     * Used for drop_down and radio button options where only one value can be selected
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option Product custom option
     * @return string|null Option type ID or null if no values exist
     */
    private function getFirstOptionValue(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option): ?string
    {
        $values = $option->getValues();
        if (empty($values)) {
            return null;
        }

        $firstValue = reset($values);
        return (string)$firstValue->getOptionTypeId();
    }

    /**
     * Get all option value IDs from a custom option as comma-separated string
     *
     * Used for checkbox and multiple select options where multiple values can be selected
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option Product custom option
     * @return string|null Comma-separated option type IDs or null if no values exist
     */
    private function getAllOptionValues(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option): ?string
    {
        $values = $option->getValues();
        if (empty($values)) {
            return null;
        }

        $valueIds = [];
        foreach ($values as $value) {
            $valueIds[] = $value->getOptionTypeId();
        }

        return implode(',', $valueIds);
    }

    /**
     * Add a product to cart with custom options via REST API
     *
     * @param string $cartId Guest cart ID
     * @param ProductInterface $product Product to add
     * @param array $customOptions Array of custom options with option_id and option_value
     * @return array Response containing the added cart item
     */
    private function addProductToCart(string $cartId, ProductInterface $product, array $customOptions): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_GUEST_CART . '/' . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        $requestData = [
            'cartItem' => [
                'quote_id' => $cartId,
                'sku' => $product->getSku(),
                'qty' => 1,
                'product_option' => [
                    'extension_attributes' => [
                        'custom_options' => $customOptions
                    ]
                ]
            ]
        ];

        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Set shipping information for the cart via REST API
     *
     * @param string $cartId Guest cart ID
     * @return array Response containing payment methods and totals
     */
    private function setShippingInformation(string $cartId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $cartId . '/shipping-information',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        $addressData = [
            'region' => 'CA',
            'region_id' => 12,
            'region_code' => 'CA',
            'country_id' => 'US',
            'street' => ['123 Test Street'],
            'postcode' => '90210',
            'city' => 'Los Angeles',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'telephone' => '555-1234'
        ];

        $requestData = [
            'addressInformation' => [
                'shipping_address' => $addressData,
                'billing_address' => $addressData,
                'shipping_carrier_code' => 'flatrate',
                'shipping_method_code' => 'flatrate'
            ]
        ];

        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Place order for the cart via REST API
     *
     * @param string $cartId Guest cart ID
     * @return int Order ID
     */
    private function placeOrder(string $cartId): int
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $cartId . '/order',
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];

        $requestData = [
            'paymentMethod' => [
                'method' => 'checkmo'
            ]
        ];

        return (int)$this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Get order details via REST API
     *
     * @param int $orderId Order ID
     * @return array Order data including entity_id, items, and other order details
     */
    private function getOrder(int $orderId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_ORDERS . '/' . $orderId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ]
        ];

        return $this->_webApiCall($serviceInfo);
    }

    /**
     * Validate custom option value format
     *
     * This method validates that the option value matches one of the expected patterns:
     * - Text string (field/area options)
     * - Numeric ID (drop_down/radio options)
     * - Comma-separated numeric IDs (checkbox/multiple options)
     * - Date/datetime/time format (YYYY-MM-DD HH:MM:SS or array structure)
     *
     * @param string|array $optionValue The option value to validate
     * @param int $index The index of the option in the array
     * @param string $sku The product SKU for error messages
     * @return void
     */
    private function validateCustomOptionValue(string|array $optionValue, int $index, string $sku): void
    {
        // Handle array option values (date/time options)
        if (is_array($optionValue)) {
            self::assertNotEmpty(
                $optionValue,
                sprintf(
                    'Custom option at index %d for item "%s" has empty array option_value',
                    $index,
                    $sku
                )
            );
            return;
        }

        // Define expected patterns for string option types
        $patterns = [
            '/^Test (field|textarea) value$/',           // Text field or textarea
            '/^\d+$/',                                    // Single numeric ID (drop_down, radio)
            '/^\d+(,\d+)*$/',                             // Comma-separated numeric IDs (checkbox, multiple)
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',  // Date/datetime/time format
        ];

        $isValid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $optionValue)) {
                $isValid = true;
                break;
            }
        }

        self::assertTrue(
            $isValid,
            sprintf(
                'Custom option at index %d for item "%s" has invalid option_value format: "%s". ' .
                'Expected one of: text string, numeric ID, comma-separated IDs, or date format (YYYY-MM-DD HH:MM:SS)',
                $index,
                $sku,
                $optionValue
            )
        );
    }

    /**
     * Get order items by order ID via REST API
     *
     * This method retrieves order items from the sales_order_item table
     * using the /V1/orders/items endpoint with order_id filter.
     * Equivalent to querying: SELECT * FROM sales_order_item WHERE order_id = ?
     *
     * @param int $orderId Order entity ID
     * @return array Array containing items, search_criteria, and total_count
     */
    private function getOrderItemsByOrderId(int $orderId): array
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filterGroups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'order_id',
                                'value' => (string)$orderId,
                                'condition_type' => 'eq'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_ORDER_ITEMS . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ]
        ];

        return $this->_webApiCall($serviceInfo, $searchCriteria);
    }
}
