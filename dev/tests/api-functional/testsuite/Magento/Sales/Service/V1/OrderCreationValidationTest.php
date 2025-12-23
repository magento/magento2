<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\User\Test\Fixture\User;
use Magento\Authorization\Test\Fixture\Role;
use Magento\Catalog\Test\Fixture\Product;

/**
 * Tests that orders cannot be created with only payment method (no billing address or items)
 */
class OrderCreationValidationTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/orders/create';

    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test the exact scenario from GitHub issue #39651
     * Should fail with validation error when trying to create order with only payment method
     */
    #[DataFixture(User::class, as: 'admin_user')]
    #[DataFixture(Role::class, as: 'admin_role')]
    public function testCreateOrderWithOnlyPaymentMethodFails(): void
    {
        // The exact payload from the GitHub issue
        $requestData = [
            'entity' => [
                'payment' => [
                    'method' => 'cashondelivery'
                ]
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected LocalizedException was not thrown');
        } catch (\Exception $e) {
            // Should throw validation error for missing billing address
            $this->assertStringContainsString(
                'Please provide billing address for the order.',
                $e->getMessage()
            );
        }
    }

    /**
     * Test creating order with only payment method and empty entity
     * Should fail with validation error
     */
    #[DataFixture(User::class, as: 'admin_user')]
    #[DataFixture(Role::class, as: 'admin_role')]
    public function testCreateOrderWithEmptyEntityFails(): void
    {
        $requestData = [
            'entity' => [
                'payment' => [
                    'method' => 'cashondelivery'
                ],
                'billing_address' => null,
                'items' => []
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected LocalizedException was not thrown');
        } catch (\Exception $e) {
            // Should throw validation error
            $this->assertTrue(
                strpos($e->getMessage(), 'Please provide billing address for the order.') !== false ||
                strpos($e->getMessage(), 'Please specify order items.') !== false,
                'Expected validation error message not found. Got: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test creating order without items but with billing address
     * Should fail with items validation error
     */
    #[DataFixture(User::class, as: 'admin_user')]
    #[DataFixture(Role::class, as: 'admin_role')]
    public function testCreateOrderWithoutItemsFails(): void
    {
        $requestData = [
            'entity' => [
                'payment' => [
                    'method' => 'cashondelivery'
                ],
                'billing_address' => [
                    'address_type' => 'billing',
                    'city' => 'CityM',
                    'country_id' => 'US',
                    'email' => 'test@example.com',
                    'firstname' => 'John',
                    'lastname' => 'Smith',
                    'postcode' => '75477',
                    'region' => 'Alabama',
                    'region_code' => 'AL',
                    'region_id' => 1,
                    'street' => ['Green str, 67'],
                    'telephone' => '3468676',
                ],
                'items' => []
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected LocalizedException was not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                'Please specify order items.',
                $e->getMessage()
            );
        }
    }

    /**
     * Test various invalid payloads that should all fail
     *
     * @dataProvider invalidOrderDataProvider
     */
    #[DataFixture(User::class, as: 'admin_user')]
    #[DataFixture(Role::class, as: 'admin_role')]
    public function testCreateOrderWithInvalidDataFails(array $requestData, string $expectedError): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected LocalizedException was not thrown for payload: ' . json_encode($requestData));
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                $expectedError,
                $e->getMessage(),
                'Expected error message not found for payload: ' . json_encode($requestData)
            );
        }
    }

    /**
     * Data provider for invalid order data scenarios
     *
     * @return array
     */
    public static function invalidOrderDataProvider(): array
    {
        return [
            'only_payment_method' => [
                [
                    'entity' => [
                        'payment' => [
                            'method' => 'cashondelivery'
                        ]
                    ]
                ],
                'Please provide billing address for the order.'
            ],
            'empty_entity' => [
                [
                    'entity' => []
                ],
                'Please provide billing address for the order.'
            ],
            'null_billing_address' => [
                [
                    'entity' => [
                        'payment' => [
                            'method' => 'cashondelivery'
                        ],
                        'billing_address' => null
                    ]
                ],
                'Please provide billing address for the order.'
            ],
            'incomplete_billing_address' => [
                [
                    'entity' => [
                        'payment' => [
                            'method' => 'cashondelivery'
                        ],
                        'billing_address' => [
                            'city' => 'Test City'
                            // Missing required fields
                        ]
                    ]
                ],
                'Please provide billing address for the order.'
            ]
        ];
    }

    /**
     * Test that the API returns 400 status code for validation errors
     */
    #[DataFixture(User::class, as: 'admin_user')]
    #[DataFixture(Role::class, as: 'admin_role')]
    public function testApiReturns400StatusCodeForValidationError(): void
    {
        $requestData = [
            'entity' => [
                'payment' => [
                    'method' => 'cashondelivery'
                ]
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];

        $this->expectException(\Exception::class);

        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Exception $e) {
            // Verify that it's not returning 200 OK (which was the original issue)
            $this->assertStringContainsString('Please provide billing address for the order.', $e->getMessage());
            // The exception should be thrown, not a successful response
            throw $e;
        }
    }

    /**
     * Test order creation with complete valid data to ensure plugin doesn't break valid orders
     */
    #[DataFixture(User::class, as: 'admin_user')]
    #[DataFixture(Role::class, as: 'admin_role')]
    #[DataFixture(Product::class, ['sku' => 'simple-test', 'price' => 25.00], 'test_product')]
    public function testCreateOrderWithValidDataSucceeds(): void
    {
        /** @var Product $product */
        $product = $this->fixtures->get('test_product');

        $requestData = [
            'entity' => [
                'base_currency_code' => 'USD',
                'base_discount_amount' => 0,
                'base_grand_total' => 25.00,
                'base_subtotal' => 25.00,
                'base_tax_amount' => 0,
                'base_total_due' => 25.00,
                'billing_address' => [
                    'address_type' => 'billing',
                    'city' => 'TestCity',
                    'country_id' => 'US',
                    'email' => 'test@example.com',
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'postcode' => '12345',
                    'region' => 'California',
                    'region_code' => 'CA',
                    'region_id' => 12,
                    'street' => ['123 Test Street'],
                    'telephone' => '555-1234',
                ],
                'customer_email' => 'test@example.com',
                'customer_firstname' => 'John',
                'customer_lastname' => 'Doe',
                'customer_is_guest' => 1,
                'discount_amount' => 0,
                'global_currency_code' => 'USD',
                'grand_total' => 25.00,
                'is_virtual' => 0,
                'order_currency_code' => 'USD',
                'state' => 'new',
                'status' => 'pending',
                'store_currency_code' => 'USD',
                'store_id' => 1,
                'subtotal' => 25.00,
                'tax_amount' => 0,
                'total_due' => 25.00,
                'total_item_count' => 1,
                'total_qty_ordered' => 1,
                'weight' => 1,
                'items' => [
                    [
                        'base_price' => $product->getPrice(),
                        'base_row_total' => $product->getPrice(),
                        'name' => $product->getName(),
                        'price' => $product->getPrice(),
                        'product_id' => $product->getId(),
                        'product_type' => $product->getTypeId(),
                        'qty_ordered' => 1,
                        'row_total' => $product->getPrice(),
                        'sku' => $product->getSku(),
                        'store_id' => 1,
                        'weight' => 1,
                    ]
                ],
                'payment' => [
                    'method' => 'cashondelivery',
                    'amount_ordered' => 25.00,
                    'base_amount_ordered' => 25.00,
                ]
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];

        // This should succeed and not throw an exception
        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('entity_id', $response);
        $this->assertArrayHasKey('increment_id', $response);
    }
}
