<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Test\Fixture\GuestCart;

class MergeCartsTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->idEncoder = Bootstrap::getObjectManager()->get(Uid::class);
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'guest'),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$guestCart.id$']),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$guestCart.id$', 'product_id' => '$product1.id$', 'qty' => 5]
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$customerCart.id$', 'product_id' => '$product1.id$', 'qty' => 3]
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$customerCart.id$', 'product_id' => '$product2.id$', 'qty' => 3]
        )
    ]
    public function testMergeCartsWithGuestPriority(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('product1')->getSku(), 'quantity' => 5],
                ['sku' => $this->fixtures->get('product2')->getSku(), 'quantity' => 3]
            ],
            8
        );
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'customer'),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$guestCart.id$']),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$guestCart.id$', 'product_id' => '$product1.id$', 'qty' => 5]
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$customerCart.id$', 'product_id' => '$product1.id$', 'qty' => 3]
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$customerCart.id$', 'product_id' => '$product2.id$', 'qty' => 3]
        )
    ]
    public function testMergeCartsWithCustomerPriority(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('product1')->getSku(), 'quantity' => 3],
                ['sku' => $this->fixtures->get('product2')->getSku(), 'quantity' => 3]
            ],
            6
        );
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'merge'),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$guestCart.id$']),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$guestCart.id$', 'product_id' => '$product1.id$', 'qty' => 5]
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$customerCart.id$', 'product_id' => '$product1.id$', 'qty' => 3]
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$customerCart.id$', 'product_id' => '$product2.id$', 'qty' => 3]
        )
    ]
    public function testMergeCartsWithMergePriority(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('product1')->getSku(), 'quantity' => 8],
                ['sku' => $this->fixtures->get('product2')->getSku(), 'quantity' => 3]
            ],
            11
        );
    }

    /**
     * Assert the response of merge carts
     *
     * @param array $products
     * @param int $totalQuantity
     * @throws AuthenticationException
     */
    private function assertMergeCartsResponse(array $products, int $totalQuantity): void
    {
        $response = $this->graphQlMutation(
            $this->getMergeCartsMutation(
                $this->fixtures->get('quoteIdMaskGuest')->getMaskedId(),
                $this->fixtures->get('quoteIdMaskCustomer')->getMaskedId()
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
        );

        $this->assertEquals($response['mergeCarts']['total_quantity'], $totalQuantity, 'Total quantity does not match');
        foreach ($products as $product) {
            foreach ($response['mergeCarts']['items'] as $item) {
                if ($item['product']['sku'] === $product['sku']) {
                    $this->assertEquals($product['quantity'], $item['quantity'], 'Item quantity does not match');
                }
            }
        }
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'guest'),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$product1$', '$product2$']],
            'configurableProduct1'
        ),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$guestCart.id$']),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$configurableProduct1.id$',
                'child_product_id' => '$product1.id$',
                'qty' => 1
            ],
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$customerCart.id$',
                'product_id' => '$configurableProduct1.id$',
                'child_product_id' => '$product1.id$',
                'qty' => 2
            ],
        )
    ]
    public function testMergeCartsWithGuestPriorityForConfigurableProducts(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('configurableProduct1')->getSku(), 'quantity' => 1]
            ],
            1
        );
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'customer'),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$product1$', '$product2$']],
            'configurableProduct1'
        ),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$guestCart.id$']),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$configurableProduct1.id$',
                'child_product_id' => '$product1.id$',
                'qty' => 1
            ],
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$customerCart.id$',
                'product_id' => '$configurableProduct1.id$',
                'child_product_id' => '$product1.id$',
                'qty' => 2
            ],
        )
    ]
    public function testMergeCartsWithCustomerPriorityForConfigurableProducts(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('configurableProduct1')->getSku(), 'quantity' => 2]
            ],
            2
        );
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'merge'),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$product1$', '$product2$']],
            'configurableProduct1'
        ),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$guestCart.id$']),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$configurableProduct1.id$',
                'child_product_id' => '$product1.id$',
                'qty' => 1
            ],
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$customerCart.id$',
                'product_id' => '$configurableProduct1.id$',
                'child_product_id' => '$product1.id$',
                'qty' => 2
            ],
        )
    ]
    public function testMergeCartsWithMergePriorityForConfigurableProducts(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('configurableProduct1')->getSku(), 'quantity' => 3]
            ],
            3
        );
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'guest'),
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], as:'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 20], as:'p2'),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0],
            as:'link1'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$p2.sku$', 'price' => 25, 'price_type' => 0],
            as:'link2'
        ),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-product-multiselect-checkbox-options','price' => 50,'price_type' => 1,
                '_options' => ['$opt1$', '$opt2$']],
            as:'bp1'
        ),
        DataFixture(Customer::class, ['email' => 'me@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$customerCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 1
            ]
        ),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),

        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testMergeCartsForBundleProductWithGuestPriority(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('bp1')->getSku(), 'quantity' => 2]
            ],
            2
        );
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'customer'),
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], as:'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 20], as:'p2'),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0],
            as:'link1'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$p2.sku$', 'price' => 25, 'price_type' => 0],
            as:'link2'
        ),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-product-multiselect-checkbox-options','price' => 50,'price_type' => 1,
                '_options' => ['$opt1$', '$opt2$']],
            as:'bp1'
        ),
        DataFixture(Customer::class, ['email' => 'me@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$customerCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 1
            ]
        ),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),

        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testMergeCartsForBundleProductWithCustomerPriority(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('bp1')->getSku(), 'quantity' => 1]
            ],
            1
        );
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'merge'),
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], as:'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 20], as:'p2'),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0],
            as:'link1'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$p2.sku$', 'price' => 25, 'price_type' => 0],
            as:'link2'
        ),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-product-multiselect-checkbox-options','price' => 50,'price_type' => 1,
                '_options' => ['$opt1$', '$opt2$']],
            as:'bp1'
        ),
        DataFixture(Customer::class, ['email' => 'me@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$customerCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 1
            ]
        ),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest'),

        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testMergeCartsForBundleProductWithMergePriority(): void
    {
        $this->assertMergeCartsResponse(
            [
                ['sku' => $this->fixtures->get('bp1')->getSku(), 'quantity' => 3]
            ],
            3
        );
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'guest'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple1',
                'options' => [
                    [
                        'title' => 'multiple option',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                        'is_require' => false,
                        'sort_order' => 5,
                        'values' => [
                            [
                                'title' => 'multiple option 1',
                                'price' => 10,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 1 sku',
                                'sort_order' => 1,
                            ],
                            [
                                'title' => 'multiple option 2',
                                'price' => 20,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku',
                                'sort_order' => 2,
                            ],
                        ],
                    ],
                    [
                        'title' => 'multiple option 2',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                        'is_require' => false,
                        'sort_order' => 5,
                        'values' => [
                            [
                                'title' => 'multiple option 2 - 1',
                                'price' => 10,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku 1',
                                'sort_order' => 1,
                            ],
                            [
                                'title' => 'multiple option 2 - 2',
                                'price' => 20,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku 2',
                                'sort_order' => 2,
                            ],
                        ],
                    ]
                ]
            ],
            'product1'
        ),
        DataFixture(Customer::class, ['email' => 'me@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest')
    ]
    public function testMergeCartsForSimpleProductsWithCustomOptionsWithGuestPriority(): void
    {
        $this->assertMergeCartsResponseForSimpleProductsWithOptions(1, 2, 2);
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'customer'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple1',
                'options' => [
                    [
                        'title' => 'multiple option',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                        'is_require' => false,
                        'sort_order' => 5,
                        'values' => [
                            [
                                'title' => 'multiple option 1',
                                'price' => 10,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 1 sku',
                                'sort_order' => 1,
                            ],
                            [
                                'title' => 'multiple option 2',
                                'price' => 20,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku',
                                'sort_order' => 2,
                            ],
                        ],
                    ],
                    [
                        'title' => 'multiple option 2',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                        'is_require' => false,
                        'sort_order' => 5,
                        'values' => [
                            [
                                'title' => 'multiple option 2 - 1',
                                'price' => 10,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku 1',
                                'sort_order' => 1,
                            ],
                            [
                                'title' => 'multiple option 2 - 2',
                                'price' => 20,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku 2',
                                'sort_order' => 2,
                            ],
                        ],
                    ]
                ]
            ],
            'product1'
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest')
    ]
    public function testMergeCartsForSimpleProductsWithCustomOptionsWithCustomerPriority(): void
    {
        $this->assertMergeCartsResponseForSimpleProductsWithOptions(1, 2, 1);
    }

    #[
        Config('checkout/cart/cart_merge_preference', 'merge'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple1',
                'options' => [
                    [
                        'title' => 'multiple option',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                        'is_require' => false,
                        'sort_order' => 5,
                        'values' => [
                            [
                                'title' => 'multiple option 1',
                                'price' => 10,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 1 sku',
                                'sort_order' => 1,
                            ],
                            [
                                'title' => 'multiple option 2',
                                'price' => 20,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku',
                                'sort_order' => 2,
                            ],
                        ],
                    ],
                    [
                        'title' => 'multiple option 2',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                        'is_require' => false,
                        'sort_order' => 5,
                        'values' => [
                            [
                                'title' => 'multiple option 2 - 1',
                                'price' => 10,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku 1',
                                'sort_order' => 1,
                            ],
                            [
                                'title' => 'multiple option 2 - 2',
                                'price' => 20,
                                'price_type' => 'fixed',
                                'sku' => 'multiple option 2 sku 2',
                                'sort_order' => 2,
                            ],
                        ],
                    ]
                ]
            ],
            'product1'
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMaskCustomer'),
        DataFixture(GuestCart::class, as: 'guestCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$guestCart.id$'], 'quoteIdMaskGuest')
    ]
    public function testMergeCartsForSimpleProductsWithCustomOptionsWithMergePriority(): void
    {
        $this->assertMergeCartsResponseForSimpleProductsWithOptions(1, 2, 3);
    }

    /**
     * Assert the response of merge carts for simple products with options
     *
     * @param int $customerQty
     * @param int $guestQty
     * @param int $totalQty
     * @throws AuthenticationException
     */
    private function assertMergeCartsResponseForSimpleProductsWithOptions(
        int $customerQty,
        int $guestQty,
        int $totalQty
    ): void {
        $sku = $this->fixtures->get('product1')->getSku();
        $productOptionsQuery = $this->getProductOptionsForQuery($sku);
        $maskedQuoteIdGuest = $this->fixtures->get('quoteIdMaskGuest')->getMaskedId();
        $maskedQuoteIdCustomer = $this->fixtures->get('quoteIdMaskCustomer')->getMaskedId();
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        $this->graphQlMutation(
            $this->getAddProductsToCartMutation($maskedQuoteIdCustomer, $customerQty, $sku, $productOptionsQuery),
            [],
            '',
            $customerAuthHeaders
        );

        $this->graphQlMutation(
            $this->getAddProductsToCartMutation($maskedQuoteIdGuest, $guestQty, $sku, $productOptionsQuery)
        );

        $response = $this->graphQlMutation(
            $this->getMergeCartsMutation($maskedQuoteIdGuest, $maskedQuoteIdCustomer),
            [],
            '',
            $customerAuthHeaders
        );
        $this->assertEquals(
            $totalQty,
            $response['mergeCarts']['total_quantity'],
            'Total quantity does not match'
        );
    }

    /**
     * Prepare product options and return
     *
     * @param string $sku
     * @return string|array|null
     */
    private function getProductOptionsForQuery(string $sku): string|array|null
    {
        $getCustomOptionsWithIDV2ForQueryBySku = Bootstrap::getObjectManager()->get(
            GetCustomOptionsWithUIDForQueryBySku::class
        );

        $itemOptions = $getCustomOptionsWithIDV2ForQueryBySku->execute($sku);

        /* The type field is only required for assertions, it should not be present in query */
        foreach ($itemOptions['entered_options'] as &$enteredOption) {
            if (isset($enteredOption['type'])) {
                unset($enteredOption['type']);
            }
        }

        return preg_replace(
            '/"([^"]+)"\s*:\s*/',
            '$1:',
            json_encode($itemOptions)
        );
    }

    /**
     * Get Merge carts mutation
     *
     * @param string $guestCartId
     * @param string $customerCartId
     * @return string
     */
    private function getMergeCartsMutation(string $guestCartId, string $customerCartId): string
    {
        return <<<MUTATION
            mutation MergeCarts {
                mergeCarts(
                    source_cart_id: "{$guestCartId}"
                    destination_cart_id: "{$customerCartId}"
                ) {
                    id
                    total_quantity
                    items {
                        quantity
                        product {
                            name
                            sku
                        }
                    }
                }
            }
        MUTATION;
    }

    /**
     * Returns the GraphQL mutation to add products to cart
     *
     * @param string $maskedQuoteId
     * @param int $qty
     * @param string $sku
     * @param string|array|null $customizableOptions
     * @return string
     */
    private function getAddProductsToCartMutation(
        string $maskedQuoteId,
        int $qty,
        string $sku,
        string|array|null $customizableOptions = '',
    ): string {
        if ($customizableOptions) {
            $customizableOptions = trim($customizableOptions, '{}');
        }

        return <<<MUTATION
            mutation {
                addProductsToCart(
                    cartId: "{$maskedQuoteId}",
                    cartItems: [
                        {
                            sku: "{$sku}"
                            quantity: {$qty}
                            {$customizableOptions}
                        }
                    ]
                ) {
                  cart {
                    items {
                      quantity
                      product {
                        name
                        sku
                      }
                    }
                  }
                }
            }
        MUTATION;
    }

    /**
     * Returns the header with customer token for GQL Mutation
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
