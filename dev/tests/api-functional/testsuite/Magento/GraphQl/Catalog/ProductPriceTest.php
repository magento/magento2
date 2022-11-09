<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Model\Configuration;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Model\LinkManagement;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Model\Group;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\GraphQl\Customer\LockCustomer;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductPriceTest extends GraphQlAbstract
{
    /** @var ObjectManager $objectManager */
    private $objectManager;

    /** @var ProductRepositoryInterface $productRepository */
    private $productRepository;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->lockCustomer = $this->objectManager->get(LockCustomer::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     */
    public function testProductWithSinglePrice()
    {
        $skus = ['simple'];
        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => 10
                ],
                "final_price" => [
                    "value" => 10
                ],
                "discount" => [
                    "amount_off" => 0,
                    "percent_off" => 0
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => 10
                ],
                "final_price" => [
                    "value" => 10
                ],
                "discount" => [
                    "amount_off" => 0,
                    "percent_off" => 0
                ]
            ]
        ];

        $this->assertPrices($expectedPriceRange, $product['price_range']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoConfigFixture default_store currency/options/allow CNY,USD
     */
    public function testProductWithSinglePriceNonDefaultCurrency()
    {
        $skus = ['simple'];
        $query = $this->getProductQuery($skus);
        $headerMap = [
            'Content-Currency' => 'CNY'
        ];
        $result = $this->graphQlQuery($query, [], '', $headerMap);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => 70
                ],
                "final_price" => [
                    "value" => 70
                ],
                "discount" => [
                    "amount_off" => 0,
                    "percent_off" => 0
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => 70
                ],
                "final_price" => [
                    "value" => 70
                ],
                "discount" => [
                    "amount_off" => 0,
                    "percent_off" => 0
                ]
            ]
        ];

        $this->assertPrices($expectedPriceRange, $product['price_range'], 'CNY');
    }

    /**
     * Pricing for Simple, Grouped and Configurable products with no special or tier prices configured
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_12345.php
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     */
    public function testMultipleProductTypes()
    {
        $skus = ["simple-1", "12345", "grouped"];

        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(3, $result['products']['items']);

        $expected = [
            "simple-1" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 10
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 10
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ]
            ],
            "12345" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 30
                    ],
                    "final_price" => [
                        "value" => 30
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 40
                    ],
                    "final_price" => [
                        "value" => 40
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ]
            ],
            "grouped" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 100
                    ],
                    "final_price" => [
                        "value" => 100
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 100
                    ],
                    "final_price" => [
                        "value" => 100
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ]
            ]
        ];

        foreach ($result['products']['items'] as $product) {
            $this->assertNotEmpty($product['price_range']);
            $this->assertPrices($expected[$product['sku']], $product['price_range']);
        }
    }

    /**
     * Simple products with special price and tier price with % discount
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @param int $customerGroup
     * @param array $expectedPriceRange
     * @param array $expectedTierPrices
     * @param array $customerData
     * @param bool $isTierPriceExists
     * @dataProvider priceDataProvider
     */
    public function testSimpleProductsWithSpecialPriceAndTierPrice(
        int $customerGroup,
        array $expectedPriceRange,
        array $expectedTierPrices,
        array $customerData
    ) {
        $skus = ["simple1", "simple2"];
        $tierPriceFactory = $this->objectManager->get(ProductTierPriceInterfaceFactory::class);

        /** @var  $tierPriceExtensionAttributesFactory */
        $tierPriceExtensionAttributesFactory = $this->objectManager->create(ProductTierPriceExtensionFactory::class);
        $tierPriceExtensionAttribute = $tierPriceExtensionAttributesFactory->create()->setPercentageValue(10);

        $tierPrices[] = $tierPriceFactory->create(
            [
                'data' => [
                    'customer_group_id' => $customerGroup,
                    'qty' => 2
                ]
            ]
        )->setExtensionAttributes($tierPriceExtensionAttribute);
        foreach ($skus as $sku) {
            /** @var Product $simpleProduct */
            $simpleProduct = $this->productRepository->get($sku);
            $simpleProduct->setTierPrices($tierPrices);
            $this->productRepository->save($simpleProduct);
        }

        $headerMap = [];
        if (!empty($customerData)) {
            $customerToken = $this->customerTokenService->createCustomerAccessToken(
                $customerData['username'],
                $customerData['password']
            );
            $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        }

        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(2, $result['products']['items']);

        foreach ($result['products']['items'] as $product) {
            $this->assertNotEmpty($product['price_range']);
            $this->assertNotEmpty($product['price_tiers']);
            $this->assertPrices($expectedPriceRange[$product['sku']], $product['price_range']);
            $this->assertResponseFields($product['price_tiers'], $expectedTierPrices[$product['sku']]);
        }
    }

    /**
     * Data provider for prices
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function priceDataProvider() : array
    {
        return [
            [
                'customer_group' => Group::CUST_GROUP_ALL,
                'expected_price_range' => [
                    "simple1" => [
                        "minimum_price" => [
                            "regular_price" => ["value" => 10],
                            "final_price" => ["value" => 5.99],
                            "discount" => ["amount_off" => 4.01, "percent_off" => 40.1]
                        ],
                        "maximum_price" => [
                            "regular_price" => ["value" => 10],
                            "final_price" => ["value" => 5.99],
                            "discount" => ["amount_off" => 4.01, "percent_off" => 40.1]
                        ]
                    ],
                    "simple2" => [
                        "minimum_price" => [
                            "regular_price" => ["value" => 20],
                            "final_price" => ["value" => 15.99],
                            "discount" => ["amount_off" => 4.01, "percent_off" => 20.05]
                        ],
                        "maximum_price" => [
                            "regular_price" => ["value" => 20],
                            "final_price" => ["value" => 15.99],
                            "discount" => ["amount_off" => 4.01, "percent_off" => 20.05]
                        ]
                    ]
                ],
                'expected_tier_prices' => [
                    "simple1" => [
                        0 => [
                            'discount' =>['amount_off' => 1, 'percent_off' => 10],
                            'final_price' =>['value'=> 9],
                            'quantity' => 2
                        ]
                    ],
                    "simple2" => [
                        0 => [
                            'discount' =>['amount_off' => 2, 'percent_off' => 10],
                            'final_price' =>['value'=> 18],
                            'quantity' => 2
                        ]
                    ]
                ],
                'customer_data' => []
            ],
            [
                'customer_group' => 1,
                'expected_price_range' => [
                    "simple1" => [
                        "minimum_price" => [
                            "regular_price" => ["value" => 10],
                            "final_price" => ["value" => 5.99],
                            "discount" => ["amount_off" => 4.01, "percent_off" => 40.1]
                        ],
                        "maximum_price" => [
                            "regular_price" => ["value" => 10],
                            "final_price" => ["value" => 5.99],
                            "discount" => ["amount_off" => 4.01, "percent_off" => 40.1]
                        ]
                    ],
                    "simple2" => [
                        "minimum_price" => [
                            "regular_price" => ["value" => 20],
                            "final_price" => ["value" => 15.99],
                            "discount" => ["amount_off" => 4.01, "percent_off" => 20.05]
                        ],
                        "maximum_price" => [
                            "regular_price" => ["value" => 20],
                            "final_price" => ["value" => 15.99],
                            "discount" => ["amount_off" => 4.01, "percent_off" => 20.05]
                        ]
                    ]
                ],
                'expected_tier_prices' => [
                    "simple1" => [
                        0 => [
                            'discount' =>['amount_off' => 1, 'percent_off' => 10],
                            'final_price' =>['value'=> 9],
                            'quantity' => 2
                        ]
                    ],
                    "simple2" => [
                        0 => [
                            'discount' =>['amount_off' => 2, 'percent_off' => 10],
                            'final_price' =>['value'=> 18],
                            'quantity' => 2
                        ]
                    ]
                ],
                'customer_data' => [
                    'username' => 'customer@example.com',
                    'password' => 'password'
                ]
            ],
        ];
    }

    /**
     * Check the pricing for a grouped product with simple products having special price set
     *
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     */
    public function testGroupedProductsWithSpecialPriceAndTierPrices()
    {
        $groupedProductSku = 'grouped';
        $grouped = $this->productRepository->get($groupedProductSku);
        //get the associated products
        $groupedProductLinks = $grouped->getProductLinks();
        $tierPriceData = [
            [
                'customer_group_id' => Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 87
            ]
        ];
        $associatedProductSkus = [];
        foreach ($groupedProductLinks as $groupedProductLink) {
            $associatedProductSkus[] = $groupedProductLink->getLinkedProductSku();
        }

        foreach ($associatedProductSkus as $associatedProductSku) {
            $associatedProduct = $this->productRepository->get($associatedProductSku);
            $associatedProduct->setSpecialPrice('95.75');
            $this->productRepository->save($associatedProduct);
            $this->saveProductTierPrices($associatedProduct, $tierPriceData);
        }
        $skus = ['grouped'];
        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => 100
                ],
                "final_price" => [
                    "value" => 95.75
                ],
                "discount" => [
                    "amount_off" => 100 - 95.75,
                    //difference between original and final over original price
                    "percent_off" => (100 - 95.75)*100/100
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => 100
                ],
                "final_price" => [
                    "value" => 95.75
                ],
                "discount" => [
                    "amount_off" => 100 - 95.75,
                    "percent_off" => (100 - 95.75)*100/100
                ]
            ]
        ];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        $this->assertEmpty($product['price_tiers']);

        // update default quantity of each of the associated products to be greater than tier price qty of each of them
        foreach ($groupedProductLinks as $groupedProductLink) {
            $groupedProductLink->getExtensionAttributes()->setQty(3);
        }
        $this->productRepository->save($grouped);
        $result = $this->graphQlQuery($query);
        $product = $result['products']['items'][0];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        $this->assertEmpty($product['price_tiers']);
    }

    /**
     * Check pricing for bundled product with one item having special price set and dynamic price turned off
     *
     * @magentoApiDataFixture Magento/Bundle/_files/product_with_multiple_options_1.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBundledProductWithSpecialPriceAndTierPrice()
    {
        $bundledProductSku = 'bundle-product';
        /** @var Product $bundled */
        $bundled = $this->productRepository->get($bundledProductSku);
        $skus = ['bundle-product'];
        $bundled->setSpecialPrice(10);

       // set the tier price for the bundled product
        $tierPriceFactory = $this->objectManager->get(ProductTierPriceInterfaceFactory::class);
       /** @var  $tierPriceExtensionAttributesFactory */
        $tierPriceExtensionAttributesFactory = $this->objectManager->create(ProductTierPriceExtensionFactory::class);
        $tierPriceExtensionAttribute = $tierPriceExtensionAttributesFactory->create()->setPercentageValue(10);
        $tierPrices[] = $tierPriceFactory->create(
            [
                'data' => [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'qty' => 2
                ]
            ]
        )->setExtensionAttributes($tierPriceExtensionAttribute);
        $bundled->setTierPrices($tierPrices);
        // Set Price view to PRICE RANGE
        $bundled->setPriceView(0);
        $this->productRepository->save($bundled);

        //Bundled product with dynamic prices turned OFF
        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);
        $this->assertNotEmpty($product['price_tiers']);

        $bundleRegularPrice = 10;
        $firstOptionPrice = 2.75;
        $secondOptionPrice = 6.75;

        $minRegularPrice = $bundleRegularPrice + $firstOptionPrice ;
        //Apply special price of 10% on minRegular price
        $minFinalPrice = round($minRegularPrice * 0.1, 2);

        $maxRegularPrice = $bundleRegularPrice + $secondOptionPrice;
        $maxFinalPrice = round($maxRegularPrice* 0.1, 2);

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => $minRegularPrice
                ],
                "final_price" => [
                    "value" => $minFinalPrice
                ],
                "discount" => [
                    "amount_off" => $minRegularPrice - $minFinalPrice,
                    "percent_off" => round(($minRegularPrice - $minFinalPrice)*100/$minRegularPrice, 2)
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => $maxRegularPrice
                ],
                "final_price" => [
                    "value" => $maxFinalPrice
                ],
                "discount" => [
                    "amount_off" => $maxRegularPrice - $maxFinalPrice,
                    "percent_off" => round(($maxRegularPrice - $maxFinalPrice)*100/$maxRegularPrice, 2)
                ]
            ]
        ];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        $this->assertResponseFields(
            $product['price_tiers'],
            [
                0 => [
                    'discount' =>[
                        'amount_off' => 1,
                        'percent_off' => 10
                    ],
                    'final_price' =>['value'=> 9],
                    'quantity' => 2
                ]
            ]
        );
    }

    /**
     * Check pricing for bundled product with spl price, tier price with dynamic price turned on
     *
     * @magentoApiDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_multiple_options.php
     */
    public function testBundledWithSpecialPriceAndTierPriceWithDynamicPrice()
    {
        $skus = ['bundle-product'];
        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);
        $this->assertNotEmpty($product['price_tiers']);

        $minRegularPrice = 10;
        $maxRegularPrice = 20;

        //Apply 10% special price on the cheapest simple product in bundle
        $minFinalPrice = round(5.99 * 0.1, 2);
        //Apply 10% special price on the expensive product in bundle
        $maxFinalPrice = round(15.99 * 0.1, 2);

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => $minRegularPrice
                ],
                "final_price" => [
                    "value" => $minFinalPrice
                ],
                "discount" => [
                    "amount_off" => $minRegularPrice - $minFinalPrice,
                    "percent_off" => round(($minRegularPrice - $minFinalPrice)*100/$minRegularPrice, 2)
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => $maxRegularPrice
                ],
                "final_price" => [
                    "value" => $maxFinalPrice
                ],
                "discount" => [
                    "amount_off" => $maxRegularPrice - $maxFinalPrice,
                    "percent_off" => round(($maxRegularPrice - $maxFinalPrice)*100/$maxRegularPrice, 2)
                ]
            ]
        ];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        $this->assertResponseFields(
            $product['price_tiers'],
            [
                0 => [
                    'discount' =>[
                        'amount_off' => 1,
                        'percent_off' => 10
                    ],
                    'final_price' =>['value'=> 0],
                    'quantity' => 2
                ]
            ]
        );
    }

    /**
     * Check pricing for Configurable product with each variants having special price and tier prices
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_12345.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testConfigurableProductWithVariantsHavingSpecialAndTierPrices()
    {
        $configurableProductSku ='12345';
        /** @var LinkManagementInterface $configurableProductLink */
        $configurableProductLinks = $this->objectManager->get(LinkManagement::class);
        $configurableProductVariants = $configurableProductLinks->getChildren($configurableProductSku);
        $tierPriceData = [
            [
                'customer_group_id' => Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 20
            ]
        ];
        foreach ($configurableProductVariants as $configurableProductVariant) {
            $configurableProductVariant->setSpecialPrice('25.99');
            $this->productRepository->save($configurableProductVariant);
            $this->saveProductTierPrices($configurableProductVariant, $tierPriceData);
        }
        $sku = ['12345'];
        $query = $this->getQueryConfigurableProductAndVariants($sku);
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);
        $regularPrice = [];
        $finalPrice = [];
        foreach ($configurableProductVariants as $configurableProductVariant) {
            $regularPrice[] = $configurableProductVariant->getPrice();
            $finalPrice[] = $configurableProductVariant->getSpecialPrice();
        }
        $regularPriceCheapestVariant = 30;
        $specialPrice = 25.99;
        $regularPriceExpensiveVariant = 40;

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => $regularPriceCheapestVariant
                ],
                "final_price" => [
                    "value" => $specialPrice
                ],
                "discount" => [
                    "amount_off" => $regularPriceCheapestVariant - $specialPrice,
                    "percent_off" => round(
                        ($regularPriceCheapestVariant - $specialPrice)*100/$regularPriceCheapestVariant,
                        2
                    )
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => $regularPriceExpensiveVariant
                ],
                "final_price" => [
                    "value" => $specialPrice
                ],
                "discount" => [
                    "amount_off" => $regularPriceExpensiveVariant - $specialPrice,
                    "percent_off" => round(
                        ($regularPriceExpensiveVariant - $specialPrice)*100/$regularPriceExpensiveVariant,
                        2
                    )
                ]
            ]
        ];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        //configurable product's tier price is empty
        $this->assertEmpty($product['price_tiers']);
        $this->assertCount(2, $product['variants']);

        $configurableVariantsInResponse = array_map(null, $product['variants'], $configurableProductVariants);

        foreach ($configurableVariantsInResponse as $key => $configurableVariantPriceData) {
            //validate that the tier prices and price range for each configurable variants are not empty
            $this->assertNotEmpty($configurableVariantPriceData[0]['product']['price_range']);
            $this->assertNotEmpty($configurableVariantPriceData[0]['product']['price_tiers']);
            $this->assertResponseFields(
                $configurableVariantsInResponse[$key][0]['product']['price_range'],
                [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => $configurableProductVariants[$key]->getPrice()
                    ],
                    "final_price" => [
                        "value" => round((float) $configurableProductVariants[$key]->getSpecialPrice(), 2)
                    ],
                    "discount" => [
                        "amount_off" => ($regularPrice[$key] - $finalPrice[$key]),
                        "percent_off" => round(($regularPrice[$key] - $finalPrice[$key])*100/$regularPrice[$key], 2)
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => $configurableProductVariants[$key]->getPrice()
                    ],
                    "final_price" => [
                        "value" => round((float) $configurableProductVariants[$key]->getSpecialPrice(), 2)
                    ],
                    "discount" => [
                        "amount_off" => $regularPrice[$key] - $finalPrice[$key],
                        "percent_off" => round(($regularPrice[$key] - $finalPrice[$key])*100/$regularPrice[$key], 2)
                    ]
                ]
                ]
            );

            $this->assertResponseFields(
                $configurableVariantsInResponse[$key][0]['product']['price_tiers'],
                [
                    0 => [
                        'discount' =>[
                            'amount_off' => $regularPrice[$key] - $tierPriceData[0]['value'],
                            'percent_off' => round(
                                (
                                    $regularPrice[$key] - $tierPriceData[0]['value']
                                    ) * 100/$regularPrice[$key],
                                2
                            )
                        ],
                        'final_price' =>['value'=> $tierPriceData[0]['value']],
                        'quantity' => 2
                    ]
                ]
            );
        }
    }

    /**
     * Check the pricing for downloadable product type
     *
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testDownloadableProductWithSpecialPriceAndTierPrices()
    {
        $downloadableProductSku = 'downloadable-product';
        /** @var Product $downloadableProduct */
        $downloadableProduct = $this->productRepository->get($downloadableProductSku);
        //setting the special price for the product
        $downloadableProduct->setSpecialPrice('5.75');
        $this->productRepository->save($downloadableProduct);
        //setting the tier price data for the product
        $tierPriceData = [
            [
                'customer_group_id' => Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 7
            ]
        ];
        $this->saveProductTierPrices($downloadableProduct, $tierPriceData);
        $skus = ['downloadable-product'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);
        $this->assertNotEmpty($product['price_tiers']);

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => 10
                ],
                "final_price" => [
                    "value" => 5.75
                ],
                "discount" => [
                    "amount_off" => 4.25,
                    //discount amount over regular price value
                    "percent_off" => (4.25/10)*100
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => 10
                ],
                "final_price" => [
                    "value" => 5.75
                ],
                "discount" => [
                    "amount_off" => 4.25,
                    "percent_off" => (4.25/10)*100
                ]
            ]
        ];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        $this->assertResponseFields(
            $product['price_tiers'],
            [
                0 => [
                    'discount' =>[
                        //regualr price - tier price value
                         'amount_off' => 3,
                         'percent_off' => 30
                    ],
                    'final_price' =>['value'=> 7],
                    'quantity' => 2
                ]
            ]
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/CatalogRule/_files/catalog_rule_10_off_not_logged.php
     */
    public function testProductWithCatalogDiscount()
    {
        $skus = ["virtual-product", "configurable"];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(2, $result['products']['items']);

        $expected = [
            "virtual-product" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 9
                    ],
                    "discount" => [
                        "amount_off" => 1,
                        "percent_off" => 10
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 9
                    ],
                    "discount" => [
                        "amount_off" => 1,
                        "percent_off" => 10
                    ]
                ]
            ],
            "configurable" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 9
                    ],
                    "discount" => [
                        "amount_off" => 1,
                        "percent_off" => 10
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 20
                    ],
                    "final_price" => [
                        "value" => 18
                    ],
                    "discount" => [
                        "amount_off" => 2,
                        "percent_off" => 10
                    ]
                ]
            ]
        ];

        foreach ($result['products']['items'] as $product) {
            $this->assertNotEmpty($product['price_range']);
            $this->assertPrices($expected[$product['sku']], $product['price_range']);
        }
    }

    /**
     * Check if the special price visible if the current date is in the date range set
     * for the special price
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_simple_product_special_price.php
     */
    public function testSpecialPriceVisibleIfInDateRange()
    {
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "simple_product"}}) {
        items {
            price_range {
                minimum_price {
                    regular_price {
                        value
                    }
                }
            }
            special_price
        }
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $productInformation = $result['products']['items'][0];
        $productRegularPrice = $productInformation['price_range']['minimum_price']['regular_price']['value'];

        self::assertEquals('10', $productRegularPrice);
        self::assertEquals('5.99', $productInformation['special_price']);
    }

    /**
     * Check if the special price is not visible if the current date is not in the date range set
     * for the special price
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_simple_product_special_price_future_date.php
     */
    public function testSpecialPriceNotVisibleIfNotInDateRange()
    {
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "simple_product"}}) {
        items {
            price_range {
                minimum_price {
                    regular_price {
                        value
                    }
                }
            }
            special_price
        }
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $productInformation = $result['products']['items'][0];
        $productRegularPrice = $productInformation['price_range']['minimum_price']['regular_price']['value'];

        self::assertEquals('10', $productRegularPrice);
        self::assertEquals(null, $productInformation['special_price']);
    }

    /**
     * Get GraphQl query to fetch products by sku
     *
     * @param array $skus
     * @return string
     */
    private function getProductQuery(array $skus): string
    {
        $stringSkus = '"' . implode('","', $skus) . '"';
        return <<<QUERY
{
  products(filter: {sku: {in: [$stringSkus]}}, sort: {name: ASC}) {
    items {
      name
      sku
      price_range {
        minimum_price {
          regular_price {
            value
            currency
          }
          final_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
        }
        maximum_price {
          regular_price {
            value
           currency
          }
          final_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
        }
      }
      price_tiers{
        discount{
          amount_off
          percent_off
        }
        final_price{
          value
        }
        quantity
      }
    }
  }
}
QUERY;
    }

    /**
     * Get GraphQl query to fetch Configurable product and its variants by sku
     *
     * @param array $sku
     * @return string
     */
    private function getQueryConfigurableProductAndVariants(array $sku): string
    {
        $stringSku = '"' . implode('","', $sku) . '"';
        return <<<QUERY
{
   products(filter: {sku: {in: [$stringSku]}}, sort: {name: ASC}) {
    items {
      name
      sku
      price_range {
        minimum_price {regular_price
        {
         value
         currency
         }
          final_price {
            value
           currency
          }
          discount {
            amount_off
            percent_off
          }
        }
        maximum_price {
          regular_price {
            value
           currency
          }
          final_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
        }
      }
      price_tiers{
              discount{
                amount_off
                percent_off
              }
              final_price{value}
                quantity
              }
      ... on ConfigurableProduct{
        variants{
          product{

            sku
            price_range {
        minimum_price {regular_price {value}
          final_price {
            value

          }
          discount {
            amount_off
            percent_off
          }
        }
        maximum_price {
          regular_price {
            value

          }
          final_price {
            value

          }
          discount {
            amount_off
            percent_off
          }
        }
      }
            price_tiers{
              discount{
                amount_off
                percent_off
              }
              final_price{value}
                quantity
              }

            }
          }
        }
      }
    }
  }

QUERY;
    }

    /**
     * Check prices from graphql response
     *
     * @param $expectedPrices
     * @param $actualPrices
     * @param string $currency
     */
    private function assertPrices($expectedPrices, $actualPrices, $currency = 'USD')
    {
        $priceTypes = ['minimum_price', 'maximum_price'];

        foreach ($priceTypes as $priceType) {
            $expected = $expectedPrices[$priceType];
            $actual = $actualPrices[$priceType];
            $this->assertEquals($expected['regular_price']['value'], $actual['regular_price']['value']);
            $this->assertEquals(
                $expected['regular_price']['currency'] ?? $currency,
                $actual['regular_price']['currency']
            );
            $this->assertEquals($expected['final_price']['value'], $actual['final_price']['value']);
            $this->assertEquals(
                $expected['final_price']['currency'] ?? $currency,
                $actual['final_price']['currency']
            );
            $this->assertEquals($expected['discount']['amount_off'], $actual['discount']['amount_off']);
            $this->assertEquals($expected['discount']['percent_off'], $actual['discount']['percent_off']);
        }
    }

    /**
     * @param ProductInterface $product
     * @param array $tierPriceData
     */
    private function saveProductTierPrices(ProductInterface $product, array $tierPriceData)
    {
        $tierPrices =[];
        $tierPriceFactory = $this->objectManager->get(ProductTierPriceInterfaceFactory::class);
        foreach ($tierPriceData as $tierPrice) {
            $tierPrices[] = $tierPriceFactory->create(
                [
                'data' => $tierPrice
                ]
            );
            /** ProductInterface $product */
            $product->setTierPrices($tierPrices);
            $product->save();
        }
    }

    /**
     * Test products with the same price reverse position with ASC and DESC sorting
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category_with_three_products.php
     */
    public function testSortByEqualPriceAndAscDescReversePosition()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple1001');
        //setting the same price for the product as all the rest have
        $product->setPrice('10');
        $this->productRepository->save($product);

        /** @var Collection $categoryCollection */
        $categoryCollection = Bootstrap::getObjectManager()->get(Collection::class);
        $category = $categoryCollection->addFieldToFilter('name', 'Category 999')->getFirstItem();
        $categoryId = (int) $category->getId();

        $expectedProductsAsc = ['simple1000', 'simple1001', 'simple1002'];
        $queryAsc = $this->getCategoryFilterPriceQuery($categoryId, 'ASC');
        $resultAsc = $this->graphQlQuery($queryAsc);
        $this->assertArrayNotHasKey('errors', $resultAsc);
        $productsAsc = array_column($resultAsc['products']['items'], 'sku');
        $this->assertEquals($expectedProductsAsc, $productsAsc);

        $expectedProductsDesc = array_reverse($expectedProductsAsc);
        $queryDesc = $this->getCategoryFilterPriceQuery($categoryId, 'DESC');
        $resultDesc = $this->graphQlQuery($queryDesc);
        $this->assertArrayNotHasKey('errors', $resultDesc);
        $productsDesc = array_column($resultDesc['products']['items'], 'sku');
        $this->assertEquals($expectedProductsDesc, $productsDesc);
    }

    /**
     * Check pricing for Configurable product with "Display Out of Stock Products" enabled
     *
     * @dataProvider configurableProductPriceRangeWithDisplayOutOfStockProductsEnabledDataProvider
     */
    #[
        Config(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, 1, ScopeInterface::SCOPE_STORE, 'default'),
        DataFixture(ProductFixture::class, ['price' => 10, 'special_price' => 7], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 18, 'special_price' => 12.6], 'p2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'],'_links' => ['$p1$','$p2$']],
            'conf1'
        ),
    ]
    public function testConfigurableProductPriceRangeWithDisplayOutOfStockProductsEnabled(
        array $productsConfiguration,
        array $expected
    ) {
        $expectedPriceRange = [
            'minimum_price' => [
                'regular_price' => [
                    'value' => $expected['regular_price'][0],
                    'currency' => 'USD',
                ],
                'final_price' => [
                    'value' => $expected['final_price'][0],
                    'currency' => 'USD',
                ],
                'discount' => [
                    'amount_off' => $expected['amount_off'][0],
                    'percent_off' => $expected['percent_off'][0],
                ],
            ],
            'maximum_price' => [
                'regular_price' => [
                    'value' => $expected['regular_price'][1],
                    'currency' => 'USD',
                ],
                'final_price' => [
                    'value' => $expected['final_price'][1],
                    'currency' => 'USD',
                ],
                'discount' => [
                    'amount_off' => $expected['amount_off'][1],
                    'percent_off' => $expected['percent_off'][1],
                ]
            ]
        ];
        foreach ($productsConfiguration as $fixture => $data) {
            $id = (int) $this->fixtures->get($fixture)->getId();
            $product = $this->productRepository->getById($id);
            $product->addData($data);
            if (isset($data['is_in_stock'])) {
                $extendedAttributes = $product->getExtensionAttributes();
                $stockItem = $extendedAttributes->getStockItem();
                $stockItem->setIsInStock($data['is_in_stock']);
                $extendedAttributes->setStockItem($stockItem);
                $product->setExtensionAttributes($extendedAttributes);
            }
            $this->productRepository->save($product);
        }
        $sku = $this->fixtures->get('conf1')->getSku();
        $query = $this->getProductQuery([$sku]);
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);
        $this->assertEquals($expectedPriceRange, $product['price_range']);
    }

    /**
     * @return array[]
     */
    public function configurableProductPriceRangeWithDisplayOutOfStockProductsEnabledDataProvider(): array
    {
        return [
            [
                [
                    'p1' => [
                        'is_in_stock' => false
                    ]
                ],
                [
                    'regular_price' => [18, 18],
                    'final_price' => [12.6, 12.6],
                    'amount_off' => [5.4, 5.4],
                    'percent_off' => [30, 30],
                ]
            ],
            [
                [
                    'p1' => [
                        'is_in_stock' => false
                    ],
                    'p2' => [
                        'status' => Status::STATUS_DISABLED
                    ]
                ],
                [
                    'regular_price' => [10, 10],
                    'final_price' => [7, 7],
                    'amount_off' => [3, 3],
                    'percent_off' => [30, 30],
                ]
            ],
            [
                [
                    'p1' => [
                        'is_in_stock' => false
                    ],
                    'p2' => [
                        'is_in_stock' => false
                    ]
                ],
                [
                    'regular_price' => [10, 18],
                    'final_price' => [7, 12.6],
                    'amount_off' => [3, 5.4],
                    'percent_off' => [30, 30],
                ]
            ]
        ];
    }

    /**
     * Query for category filter price
     *
     * @param int $categoryId
     * @param string $direction
     * @return string
     */
    protected function getCategoryFilterPriceQuery(int $categoryId, string $direction): string
    {
        $query = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {price: $direction}) {
    total_count
    items {
      sku
      name
    }
  }
}
QUERY;

        return $query;
    }
}
