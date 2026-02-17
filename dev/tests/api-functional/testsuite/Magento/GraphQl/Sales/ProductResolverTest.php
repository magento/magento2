<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\AppArea;

class ProductResolverTest extends GraphQlAbstract
{
    private const PRODUCT_PRICE = 100;
    private const PRODUCT_QTY = 2;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test successful product resolution from order item for customer order
     *
     * @return void
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, [
            'name' => 'Test Product',
            'price' => self::PRODUCT_PRICE,
            'description' => 'Test product description',
            'short_description' => 'Test short description'
        ], 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$',
                'qty' => self::PRODUCT_QTY
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testProductResolverForCustomerOrder(): void
    {
        /** @var OrderInterface $order */
        $order = $this->fixtures->get('order');
        $product = $this->fixtures->get('product');

        $this->assertEquals(
            [
                'customer' => [
                    'orders' => [
                        'items' => [
                            [
                                'number' => $order->getIncrementId(),
                                'items' => [
                                    [
                                        'product_name' => $product->getName(),
                                        'product_sku' => $product->getSku(),
                                        'quantity_ordered' => (float)self::PRODUCT_QTY,
                                        'product' => [
                                            'name' => $product->getName(),
                                            'sku' => $product->getSku(),
                                            '__typename' => 'SimpleProduct'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerOrderWithProductQuery($order->getIncrementId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Test successful product resolution from order item for guest order
     *
     * @return void
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, [
            'name' => 'Guest Test Product',
            'price' => self::PRODUCT_PRICE,
            'description' => 'Guest test product description'
        ], 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => self::PRODUCT_QTY
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order')
    ]
    public function testProductResolverForGuestOrder(): void
    {
        /** @var OrderInterface $order */
        $order = $this->fixtures->get('order');
        $product = $this->fixtures->get('product');

        $this->assertEquals(
            [
                'guestOrder' => [
                    'number' => $order->getIncrementId(),
                    'items' => [
                        [
                            'product_name' => $product->getName(),
                            'product_sku' => $product->getSku(),
                            'quantity_ordered' => (float)self::PRODUCT_QTY,
                            'product' => [
                                'name' => $product->getName(),
                                'sku' => $product->getSku(),
                                '__typename' => 'SimpleProduct'
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getGuestOrderWithProductQuery(),
                [
                    'input' => [
                        'number' => $order->getIncrementId(),
                        'email' => $order->getBillingAddress()->getEmail(),
                        'lastname' => $order->getBillingAddress()->getLastname()
                    ]
                ]
            )
        );
    }

    /**
     * Test product resolution with deleted product returns null
     *
     * @return void
     * @throws Exception
     */
    #[
        AppArea('adminhtml'),
        DataFixture(ProductFixture::class, [
            'name' => 'To Be Deleted Product',
            'price' => self::PRODUCT_PRICE
        ], 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$',
                'qty' => self::PRODUCT_QTY
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testProductResolverForCustomerOrderWithDeletedProduct(): void
    {
        /** @var OrderInterface $order */
        $order = $this->fixtures->get('order');
        $product = $this->fixtures->get('product');

        // Delete the product after order is placed to simulate the scenario
        Bootstrap::getObjectManager()
            ->get(ProductRepositoryInterface::class)
            ->deleteById($product->getSku());

        $this->assertEquals(
            [
                'customer' => [
                    'orders' => [
                        'items' => [
                            [
                                'number' => $order->getIncrementId(),
                                'items' => [
                                    [
                                        'product_name' => $product->getName(),
                                        'product_sku' => $product->getSku(),
                                        'quantity_ordered' => (float)self::PRODUCT_QTY,
                                        'product' => null
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerOrderWithProductQuery($order->getIncrementId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Test successful product resolution with additional product fields
     *
     * @return void
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, [
            'name' => 'Detailed Test Product',
            'price' => self::PRODUCT_PRICE,
            'description' => 'Detailed test product description',
            'short_description' => 'Detailed test short description',
            'weight' => 2.5,
            'status' => 1,
            'visibility' => 4,
            'type_id' => 'simple'
        ], 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$',
                'qty' => self::PRODUCT_QTY
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testProductResolverForCustomerOrderWithDetailedFields(): void
    {
        /** @var OrderInterface $order */
        $order = $this->fixtures->get('order');
        $product = $this->fixtures->get('product');

        $this->assertEquals(
            [
                'customer' => [
                    'orders' => [
                        'items' => [
                            [
                                'number' => $order->getIncrementId(),
                                'items' => [
                                    [
                                        'product_name' => $product->getName(),
                                        'product_sku' => $product->getSku(),
                                        'quantity_ordered' => (float)self::PRODUCT_QTY,
                                        'product' => [
                                            'name' => $product->getName(),
                                            'sku' => $product->getSku(),
                                            'description' => [
                                                'html' => $product->getDescription()
                                            ],
                                            'short_description' => [
                                                'html' => $product->getShortDescription()
                                            ],
                                            'type_id' => $product->getTypeId(),
                                            'weight' => (float)$product->getWeight(),
                                            '__typename' => 'SimpleProduct'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerOrderWithDetailedProductQuery($order->getIncrementId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Get customer orders query with basic product fields
     *
     * @param string $orderId
     * @return string
     */
    private function getCustomerOrderWithProductQuery(string $orderId): string
    {
        return <<<QUERY
            query Customer {
                customer {
                    orders(filter: { number: { eq: "{$orderId}" } }) {
                        items {
                            number
                            items {
                                product_name
                                product_sku
                                quantity_ordered
                                product {
                                    name
                                    sku
                                    __typename
                                }
                            }
                        }
                    }
                }
            }
        QUERY;
    }

    /**
     * Get guest order query with product fields
     *
     * @return string
     */
    private function getGuestOrderWithProductQuery(): string
    {
        return <<<QUERY
            query GuestOrder(\$input: GuestOrderInformationInput!) {
                guestOrder(input: \$input) {
                    number
                    items {
                        product_name
                        product_sku
                        quantity_ordered
                        product {
                            name
                            sku
                            __typename
                        }
                    }
                }
            }
        QUERY;
    }

    /**
     * Get customer orders query with detailed product fields
     *
     * @param string $orderId
     * @return string
     */
    private function getCustomerOrderWithDetailedProductQuery(string $orderId): string
    {
        return <<<QUERY
            query Customer {
                customer {
                    orders(filter: { number: { eq: "{$orderId}" } }) {
                        items {
                            number
                            items {
                                product_name
                                product_sku
                                quantity_ordered
                                product {
                                    name
                                    sku
                                    description {
                                        html
                                    }
                                    short_description {
                                        html
                                    }
                                    type_id
                                    ... on SimpleProduct {
                                        weight
                                    }
                                    __typename
                                }
                            }
                        }
                    }
                }
            }
        QUERY;
    }

    /**
     * Returns the header with customer token for GraphQL query
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
