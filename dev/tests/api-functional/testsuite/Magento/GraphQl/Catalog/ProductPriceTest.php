<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Bundle\Model\Product\OptionList;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Group;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductPriceTest extends GraphQlAbstract
{

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
     * Pricing for Simple, Grouped and Configurable products
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
     */
    public function testSimpleProductsWithSpecialPriceAndTierPrice()
    {
        $skus = ["simple1", "simple2"];
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
        /** @var  $tpExtensionAttributes */
        $tierPriceExtensionAttributesFactory = $objectManager->create(ProductTierPriceExtensionFactory::class);
        $tierPriceExtensionAttribute = $tierPriceExtensionAttributesFactory->create()->setPercentageValue(10);

        $tierPrices[] = $tierPriceFactory->create(
            [
                'data' => [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'qty' => 2
                ]
            ]
        )->setExtensionAttributes($tierPriceExtensionAttribute);
        foreach ($skus as $sku) {
            /** @var Product $simpleProduct */
            $simpleProduct = $productRepository->get($sku);
            $simpleProduct->setTierPrices($tierPrices);
            $productRepository->save($simpleProduct);
        }
        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(2, $result['products']['items']);

        $expectedPriceRange = [
            "simple1" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 5.99
                    ],
                    "discount" => [
                        "amount_off" => 4.01,
                        "percent_off" => 40.1
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 5.99
                    ],
                    "discount" => [
                        "amount_off" => 4.01,
                        "percent_off" => 40.1
                    ]
                ]
            ],
            "simple2" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 20
                    ],
                    "final_price" => [
                        "value" => 15.99
                    ],
                    "discount" => [
                        "amount_off" => 4.01,
                        "percent_off" => 20.05
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 20
                    ],
                    "final_price" => [
                        "value" => 15.99
                    ],
                    "discount" => [
                        "amount_off" => 4.01,
                        "percent_off" => 20.05
                    ]
                ]
            ]
        ];
        $expectedTierPrices = [
            "simple1" => [
                0 => [
                    'discount' =>[
                        'amount_off' => 1,
                        'percent_off' => 10
                    ],
                    'final_price' =>['value'=> 9],
                    'quantity' => 2
                ]
            ],
            "simple2" => [
                0 => [
                    'discount' =>[
                        'amount_off' => 2,
                        'percent_off' => 10
                    ],
                    'final_price' =>['value'=> 18],
                    'quantity' => 2
                ]

            ]
        ];

        foreach ($result['products']['items'] as $product) {
            $this->assertNotEmpty($product['price_range']);
            $this->assertNotEmpty($product['price_tiers']);
            $this->assertPrices($expectedPriceRange[$product['sku']], $product['price_range']);
            $this->assertResponseFields($product['price_tiers'], $expectedTierPrices[$product['sku']]);
        }
    }

    /**
     * Check the pricing for a grouped product with simple products having special price set
     *
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     */
    public function testGroupedProductsWithSpecialPriceAndTierPrices()
    {
        $groupedProductSku = 'grouped';
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $grouped = $productRepository->get($groupedProductSku);
        //get the associated products
        $groupedProductLinks = $grouped->getProductLinks();
        $associatedProductSkus = [];
        $tierPriceData = [
            [
                'customer_group_id' => Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 87
            ]
        ];
        foreach ($groupedProductLinks as $groupedProductLink) {
            $associatedProductSkus[] = $groupedProductLink ->getLinkedProductSku();
        }
        $associatedProduct = [];
        foreach ($associatedProductSkus as $associatedProductSku) {
            $associatedProduct = $productRepository->get($associatedProductSku);
            $associatedProduct->setSpecialPrice('95.75');
            $productRepository->save($associatedProduct);
            $this->saveProductTierPrices($associatedProduct, $tierPriceData);
        }

        //select one of the simple associated products and add a special price
       // $associatedProductSku = $groupedProductLinks[0]->getLinkedProductSku();
        /** @var Product $associatedProduct */
//        $associatedProduct = $productRepository->get($associatedProductSku);
//        $associatedProduct->setSpecialPrice('95.75');
//        $productRepository->save($associatedProduct);
        $skus = ['grouped'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);
        $discountAmount = $associatedProduct->getPrice() - $associatedProduct->getSpecialPrice();
        $percentageDiscount = $discountAmount;

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => $associatedProduct->getPrice()
                ],
                "final_price" => [
                    "value" => $associatedProduct->getSpecialPrice()
                ],
                "discount" => [
                    "amount_off" => $discountAmount,
                    "percent_off" => $percentageDiscount
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => $associatedProduct->getPrice()
                ],
                "final_price" => [
                    "value" => $associatedProduct->getSpecialPrice()
                ],
                "discount" => [
                    "amount_off" => $discountAmount,
                    "percent_off" => $percentageDiscount
                ]
            ]
        ];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        $this->assertEmpty($product['price_tiers']);

        // update default quantity of each of the associated products to be greater than tier price qty of each of them
        foreach ($groupedProductLinks as $groupedProductLink) {
            $groupedProductLink->getExtensionAttributes()->setQty(3);
        }
        $productRepository->save($grouped);
        $result = $this->graphQlQuery($query);
        $product = $result['products']['items'][0];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        $this->assertEmpty($product['price_tiers']);
    }

    /**
     * Check pricing for bundled product with one of bundle items having special price set and no dynamic price type set
     *
     * @magentoApiDataFixture Magento/Bundle/_files/product_with_multiple_options_1.php
     */
    public function testBundledProductWithSpecialPriceAndTierPrice()
    {
        $bundledProductSku = 'bundle-product';
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $bundled */
        $bundled = $productRepository->get($bundledProductSku);
        $skus = ['bundle-product'];
        $query = $this->getProductQuery($skus);
        $bundled->setSpecialPrice(10);
        // Price view set to price_range
        $bundled->setPriceView(0);
        $productRepository->save($bundled);
        $bundleRegularPrice = $bundled->getPrice();
        /** @var OptionList $optionList */
        $optionList = $objectManager->get(\Magento\Bundle\Model\Product\OptionList::class);
        $options = $optionList->getItems($bundled);
        $option = $options[0];
        /** @var \Magento\Bundle\Api\Data\LinkInterface $bundleProductLinks */
        $bundleProductLinks = $option->getProductLinks();
        $firstOptionPrice = $bundleProductLinks[0]->getPrice();
        $secondOptionPrice = $bundleProductLinks[1]->getPrice();

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);

        //special price of 10% discount
        $minRegularPrice = $bundleRegularPrice + $firstOptionPrice ;
        //10% discount(caused by special price) of minRegular price
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
    }

    /**
     * Check the pricing for bundled product with one of bundle items having special price set
     *
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     *
     */
    public function testDownloadableProductWithSpecialPriceAndTierPrices()
    {
        $downloadableProductSku = 'downloadable-product';
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $downloadableProduct */
        $downloadableProduct = $productRepository->get($downloadableProductSku);
        //setting the special price for the product
        $downloadableProduct->setSpecialPrice('5.75');
        $productRepository->save($downloadableProduct);
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
        $discountAmount = $downloadableProduct->getPrice() - $downloadableProduct->getSpecialPrice();
        $percentageDiscount = ($discountAmount/$downloadableProduct->getPrice())*100;

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => $downloadableProduct->getPrice()
                ],
                "final_price" => [
                    "value" => $downloadableProduct->getSpecialPrice()
                ],
                "discount" => [
                    "amount_off" => $discountAmount,
                    "percent_off" => $percentageDiscount
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => $downloadableProduct->getPrice()
                ],
                "final_price" => [
                    "value" => $downloadableProduct->getSpecialPrice()
                ],
                "discount" => [
                    "amount_off" => $discountAmount,
                    "percent_off" => $percentageDiscount
                ]
            ]
        ];
        $this->assertPrices($expectedPriceRange, $product['price_range']);
        $this->assertResponseFields(
            $product['price_tiers'],
            [
                0 => [
                    'discount' =>[
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
        $objectManager = Bootstrap::getObjectManager();
        $tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
        foreach ($tierPriceData as $tierPrice) {
            $tierPrices[] = $tierPriceFactory->create([
                'data' => $tierPrice
            ]);
            /** ProductInterface $product */
            $product->setTierPrices($tierPrices);
            $product->save();
        }
    }
}
