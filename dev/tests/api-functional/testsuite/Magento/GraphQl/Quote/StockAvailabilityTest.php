<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class StockAvailabilityTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ProductRepositoryInterface|mixed
     */
    private $productRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    private const PARENT_SKU_BUNDLE = 'parent_bundle';
    private const PARENT_SKU_CONFIGURABLE = 'parent_configurable';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->stockRegistry = Bootstrap::getObjectManager()->get(StockRegistryInterface::class);
    }

    #[
        Config('cataloginventory/options/not_available_message', 0),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusUnavailableSimpleProduct(): void
    {
        $this->updateProductStock();

        $this->assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            [
                                'is_available' => false,
                                'not_available_message' => 'Not enough items for sale',
                                'product' => [
                                    'sku' => $this->fixtures->get('product')->getSku(),
                                    'only_x_left_in_stock' => null,
                                ],
                                'quantity' => 100,
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId())
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusAvailableSimpleProduct(): void
    {
        $this->assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            [
                                'is_available' => true,
                                'not_available_message' => null,
                                'product' => [
                                    'sku' => $this->fixtures->get('product')->getSku(),
                                    'only_x_left_in_stock' => 100,
                                ],
                                'quantity' => 100
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId())
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusUnavailableSimpleProductOption1(): void
    {
        $this->updateProductStock(10, true);

        $this->assertEquals(
            [
                'addProductsToCart' => [
                    'cart' => [
                        'itemsV2' => [
                            'items' => [],
                        ],
                    ],
                    'user_errors' => [
                        [
                            'code' => 'INSUFFICIENT_STOCK',
                            'message' => 'Only 10 of 20 available',
                        ]
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->addToCartMutation(
                    $this->fixtures->get('quoteIdMask')->getMaskedId(),
                    $this->fixtures->get('product')->getSku(),
                    20
                )
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 99]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusAddSimpleProduct(): void
    {
        $this->assertEquals(
            [
                'addProductsToCart' => [
                    'cart' => [
                        'itemsV2' => [
                            'items' => [
                                [
                                    'not_available_message' => null,
                                    'quantity' => 100,
                                    'is_available' => true,
                                ]
                            ]
                        ]
                    ],
                    'user_errors' => [],
                ],
            ],
            $this->graphQlMutation(
                $this->addToCartMutation(
                    $this->fixtures->get('quoteIdMask')->getMaskedId(),
                    $this->fixtures->get('product')->getSku()
                )
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 0),
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product.sku$', 'price' => 100, 'price_type' => 0
            ],
            as:'link'
        ),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link$']], 'option'),
        DataFixture(
            BundleProductFixture::class,
            ['price' => 90, '_options' => ['$option$']],
            as:'bundleProduct'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundleProduct.id$',
                'selections' => [['$product.id$']],
                'qty' => 100
            ],
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusUnavailableBundleProduct(): void
    {
        $this->updateProductStock();

        $this->assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            [
                                'is_available' => null,
                                'not_available_message' => 'Not enough items for sale',
                                'quantity' => 100,
                                'product' => [
                                    'sku' => $this->fixtures->get('bundleProduct')->getSku(),
                                    'only_x_left_in_stock' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId())
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product.sku$', 'price' => 100, 'price_type' => 0
            ],
            as:'link'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'Checkbox Options',
                'type' => 'checkbox',
                'required' => 1,
                'product_links' => ['$link$']
            ],
            'option'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => self::PARENT_SKU_BUNDLE,
                'price' => 90,
                '_options' => ['$option$']
            ],
            as:'bundleProduct'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundleProduct.id$',
                'selections' => [
                    ['$product.id$']
                ],
                'qty' => 99
            ],
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusAddBundleProduct(): void
    {
        $product = $this->productRepository->get(self::PARENT_SKU_BUNDLE);

        /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        /** @var $option \Magento\Bundle\Model\Option */
        $option = $typeInstance->getOptionsCollection($product)->getFirstItem();
        /** @var \Magento\Catalog\Model\Product $selection */
        $selection = $typeInstance->getSelectionsCollection([$option->getId()], $product)->getFirstItem();

        $bundleOptionIdV2 = $this->generateBundleOptionIdV2(
            (int) $option->getId(),
            (int) $selection->getSelectionId(),
            1
        );

        $this->assertEquals(
            [
                'addProductsToCart' => [
                    'cart' => [
                        'itemsV2' => [
                            'items' => [
                                [
                                    'is_available' => true,
                                    'not_available_message' => null,
                                    'product' => [
                                        'sku' => self::PARENT_SKU_BUNDLE,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->mutationAddBundleProduct(
                    $this->fixtures->get('quoteIdMask')->getMaskedId(),
                    self::PARENT_SKU_BUNDLE,
                    $bundleOptionIdV2
                )
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 0),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$product$']],
            'configurable_product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable_product.id$',
                'child_product_id' => '$product.id$',
                'qty' => 100
            ],
        )
    ]
    public function testStockStatusUnavailableConfigurableProduct(): void
    {
        $this->updateProductStock();
        $this->assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            [
                                'is_available' => false,
                                'not_available_message' => 'Not enough items for sale',
                                'quantity' => 100,
                                'product' => [
                                    'sku' => $this->fixtures->get('configurable_product')->getSku(),
                                    'only_x_left_in_stock' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId())
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$product$']],
            'configurable_product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable_product.id$',
                'child_product_id' => '$product.id$',
                'qty' => 90
            ],
        ),
    ]
    public function testStockStatusAvailableConfigurableProduct(): void
    {
        $this->assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            [
                                'is_available' => true,
                                'not_available_message' => null,
                                'quantity' => 90,
                                'product' => [
                                    'sku' => $this->fixtures->get('configurable_product')->getSku(),
                                    'only_x_left_in_stock' => 100,
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId())
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'product_variant_1',
            ],
            'product_variant_1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'product_variant_2',
            ],
            'product_variant_2'
        ),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'type_id' => 'simple',
                'sku' => self::PARENT_SKU_CONFIGURABLE,
                '_options' => [
                    '$attribute$'
                ],
                '_links' => [
                    '$product_variant_1$',
                    '$product_variant_2$',
                ],
            ],
            'configurable_product'
        ),
        DataFixture(
            ProductStockFixture::class,
            [
                'prod_id' => '$product_variant_1.id$',
                'prod_qty' => 100
            ],
            'productVariantStock1'
        ),
        DataFixture(
            ProductStockFixture::class,
            [
                'prod_id' => '$product_variant_2.id$',
                'prod_qty' => 100
            ],
            'productVariantStock2'
        ),

        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testStockStatusAddConfigurableProduct(): void
    {
        $productVariant1 =  $this->fixtures->get('product_variant_1');
        /** @var AttributeInterface $attribute */
        $attribute = $this->fixtures->get('attribute');
        /** @var AttributeOptionInterface $option */
        $option = $attribute->getOptions()[1];
        $selectedOption = base64_encode("configurable/{$attribute->getAttributeId()}/{$option->getValue()}");

        $this->assertEquals(
            [
                'addProductsToCart' => [
                    'cart' => [
                        'itemsV2' => [
                            'items' => [
                                [
                                    'quantity' => 100,
                                    'is_available' => 1,
                                    'not_available_message' => '',
                                    'product' => [
                                        'sku' => 'product_variant_1',
                                        'only_x_left_in_stock' => 100,
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'user_errors' => [],
                ],
            ],
            $this->graphQlMutation(
                $this->mutationAddConfigurableProduct(
                    $this->fixtures->get('quoteIdMask')->getMaskedId(),
                    $productVariant1->getData('sku'),
                    $selectedOption,
                    100
                )
            )
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testNotAvailableMessageOption1(): void
    {
        $this->updateProductStock(90, true);
        $this->assertEquals(
            [
                'addProductsToCart' => [
                    'cart' => [
                        'itemsV2' => [
                            'items' => [],
                        ],
                    ],
                    'user_errors' => [
                        [
                            'code' => 'INSUFFICIENT_STOCK',
                            'message' => 'Only 90 of 100 available',
                        ]
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->addToCartMutation(
                    $this->fixtures->get('quoteIdMask')->getMaskedId(),
                    $this->fixtures->get('product')->getSku(),
                    100
                )
            )
        );
    }

    /**
     * Generate GraphQL query to get cart items with availability status
     *
     * @param string $cartId
     * @return string
     */
    private function getCartQuery(string $cartId): string
    {
        return <<<QUERY
            {
              cart(cart_id:"{$cartId}") {
                itemsV2 {
                  items {
                    is_available
                    not_available_message
                    quantity
                    product {
                        sku
                        only_x_left_in_stock
                    }
                  }
                }
              }
            }
        QUERY;
    }

    /**
     * Generate GraphQL mutation for adding bundle product to cart
     *
     * @param string $cartId
     * @param string $sku
     * @param string $bundleOptionIdV2
     * @param int $qty
     * @return string
     */
    private function mutationAddBundleProduct(
        string $cartId,
        string $sku,
        string $bundleOptionIdV2,
        int $qty = 1
    ): string {
        return <<<MUTATION
            mutation {
              addProductsToCart(
                cartId: "{$cartId}",
                cartItems: [
                {
                  sku: "{$sku}"
                  quantity: $qty
                  selected_options: [
                    "{$bundleOptionIdV2}"
                  ]
                }]
              ) {
                cart {
                  itemsV2 {
                    items {
                      is_available
                      not_available_message
                      product {
                        sku
                      }
                    }
                  }
                }
              }
            }
        MUTATION;
    }

    /**
     * Generate GraphQL mutation for adding configurable product to cart
     *
     * @param string $cartId
     * @param string $sku
     * @param string $selectedOption
     * @param int $qty
     * @return string
     */
    private function mutationAddConfigurableProduct(
        string $cartId,
        string $sku,
        string $selectedOption,
        int $qty = 1
    ): string {
        return <<<MUTATION
            mutation {
              addProductsToCart(
                cartId: "{$cartId}",
                cartItems: [
                {
                  sku: "{$sku}"
                  quantity: $qty
                  selected_options: [
                    "$selectedOption"
                  ]
                }]
              ) {
              cart {
                itemsV2 {
                  items {
                    quantity
                    is_available
                    not_available_message
                    product {
                      sku
                      only_x_left_in_stock
                    }
                  }
                  }
                }
                user_errors {
                  code
                  message
                }
              }
            }
        MUTATION;
    }

    /**
     * Generate GraphQL mutation for adding product to cart
     *
     * @param string $cartId
     * @param string $sku
     * @param int $qty
     * @return string
     */
    private function addToCartMutation(string $cartId, string $sku, int $qty = 1): string
    {
        return <<<MUTATION
            mutation{
               addProductsToCart(cartId: "{$cartId}",
                  cartItems:[
                    {
                      sku:"{$sku}"
                      quantity:{$qty}
                    }
                  ]
            )
              {
                cart {
                  itemsV2 {
                    items {
                      not_available_message
                      quantity
                      is_available
                    }
                  }
                }
                user_errors{
                    code
                    message
                }
              }
            }
        MUTATION;
    }

    /**
     * Generate bundle option ID for v2 format
     *
     * @param int $optionId
     * @param int $selectionId
     * @param int $quantity
     * @return string
     */
    private function generateBundleOptionIdV2(int $optionId, int $selectionId, int $quantity): string
    {
        return base64_encode("bundle/$optionId/$selectionId/$quantity");
    }

    /**
     * Update product stock to specified quantity and stock status
     *
     * @param int $qty
     * @param bool $isInStock
     * @return void
     * @throws Exception
     */
    private function updateProductStock(int $qty = 0, bool $isInStock = false): void
    {
        /** @var ProductInterface $product */
        $product = $this->fixtures->get('product');
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        $stockItem->setData(StockItemInterface::IS_IN_STOCK, $isInStock);
        $stockItem->setData(StockItemInterface::QTY, $qty);
        $stockItem->setData(StockItemInterface::MANAGE_STOCK, true);
        $stockItem->save();
    }
}
