<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test getting CartItemPrices schema for catalog_discount and row_catalog_discount
 */
class CatalogDiscountTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => '10% Discount Rule',
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
                'conditions' => [],
                'actions' => [],
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'is_active' => 1
            ],
            as: 'catalog_rule'
        ),
        DataFixture(ProductFixture::class, ['type_id' => 'simple', 'price' => 200], as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testCartItemPricesForSimpleProduct(): void
    {
        self::assertEquals(
            [
                'cart' => [
                    'items' => [
                        0 => [
                            'prices' => [
                                'catalog_discount' => [
                                    'percent_off' => 10,
                                    'amount_off' => 20
                                ],
                                'row_catalog_discount' => [
                                    'percent_off' => 10,
                                    'amount_off' => 40
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => '10% Discount Rule',
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
                'conditions' => [],
                'actions' => [],
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'is_active' => 1
            ],
            as: 'catalog_rule'
        ),
        DataFixture(ProductFixture::class, ['type_id' => 'simple', 'price' => 300], as: 'configProd1'),
        DataFixture(ProductFixture::class, ['type_id' => 'simple', 'price' => 600], as: 'configProd2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$configProd1$', '$configProd2$']],
            'configurableProduct'
        ),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurableProduct.id$',
                'child_product_id' => '$configProd1.id$',
                'qty' => 2
            ]
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testCartItemPricesForConfigurableProduct(): void
    {
        self::assertEquals(
            [
                'cart' => [
                    'items' => [
                        0 => [
                            'prices' => [
                                'catalog_discount' => [
                                    'percent_off' => 10,
                                    'amount_off' => 30
                                ],
                                'row_catalog_discount' => [
                                    'percent_off' => 10,
                                    'amount_off' => 60
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => '10% Discount Rule',
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
                'conditions' => [],
                'actions' => [],
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'is_active' => 1
            ],
            as: 'catalog_rule'
        ),
        DataFixture(ProductFixture::class, ['price' => 200], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 100], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => ['$opt1$', '$opt2$']
            ],
            'bundle_product_1'
        ),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testCartItemPricesForBundleProduct(): void
    {
        self::assertEquals(
            [
                'cart' => [
                    'items' => [
                        0 => [
                            'prices' => [
                                'catalog_discount' => [
                                    'percent_off' => 10,
                                    'amount_off' => 30
                                ],
                                'row_catalog_discount' => [
                                    'percent_off' => 10,
                                    'amount_off' => 60
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => '10% Discount Rule',
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
                'conditions' => [],
                'actions' => [],
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'is_active' => 1
            ],
            as: 'catalog_rule'
        ),
        DataFixture(ProductFixture::class, ['price' => 200, 'special_price' => 150], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 100], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => ['$opt1$', '$opt2$']
            ],
            'bundle_product_1'
        ),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 1
            ]
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testCartItemPricesForBundleProductWithSpecialPrice(): void
    {
        self::assertEquals(
            [
                'cart' => [
                    'items' => [
                        0 => [
                            'prices' => [
                                'catalog_discount' => [
                                    'percent_off' => 20,
                                    'amount_off' => 60
                                ],
                                'row_catalog_discount' => [
                                    'percent_off' => 20,
                                    'amount_off' => 60
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Generates GraphQl query for retrieving cart items prices
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
            {
              cart(cart_id: "$maskedQuoteId") {
                items {
                  prices {
                    catalog_discount {
                      percent_off
                      amount_off
                    }
                    row_catalog_discount {
                      percent_off
                      amount_off
                    }
                  }
                }
              }
            }
        QUERY;
    }

    /**
     * Returns the header with customer token for GQL Query
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
