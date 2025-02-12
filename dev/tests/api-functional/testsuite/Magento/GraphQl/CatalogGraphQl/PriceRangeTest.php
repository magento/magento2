<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQl;

use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Tax\Test\Fixture\ProductTaxClass;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;

/**
 * Test class to verify catalog price rule is applied for
 * tier prices for different customer groups.
 */
class PriceRangeTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager|null
     */
    private $objectManager;

    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getCustomerAuthenticationHeader = $this->objectManager->get(GetCustomerAuthenticationHeader::class);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_in_multiple_websites.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_in_multiple_websites_with_special_price.php
     */
    public function testMinimalPriceForConfigurableProductWithSpecialPrice() : void
    {
        $headerMapFirstStore['Store'] = 'default';
        $headerMapSecondStore['Store'] = 'fixture_second_store';
        $query = $this->getProductsBySkuQuery();
        $responseForFirstWebsite = $this->graphQlQuery($query, [], '', $headerMapFirstStore);
        $responseForSecondWebsite = $this->graphQlQuery($query, [], '', $headerMapSecondStore);

        $this->assertNotEmpty($responseForFirstWebsite['products']);
        $priceRange = $responseForFirstWebsite['products']['items'][0]['price_range'];
        $this->assertEquals(10, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(10, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(10, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(10, $priceRange['maximum_price']['final_price']['value']);

        $this->assertNotEmpty($responseForSecondWebsite['products']);
        $priceRange = $responseForSecondWebsite['products']['items'][0]['price_range'];
        $this->assertEquals(20, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(4, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(20, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(4, $priceRange['maximum_price']['final_price']['value']);
    }

    /**
     * Test for checking if catalog rule price has been applied for all customer group
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/CatalogRule/_files/catalog_rule_25_customer_group_all.php
     */
    public function testCheckIfCatalogRuleIsAppliedForTierPriceForAllGroups(): void
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);

        $response = $this->graphQlQuery($query);

        $this->assertNotEmpty($response['products']);
        $priceRange = $response['products']['items'][0]['price_range'];
        $this->assertEquals(10, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(7.5, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(2.5, $priceRange['minimum_price']['discount']['amount_off']);
        $this->assertEquals(25, $priceRange['minimum_price']['discount']['percent_off']);
        $this->assertEquals(10, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(7.5, $priceRange['maximum_price']['final_price']['value']);
        $this->assertEquals(2.5, $priceRange['maximum_price']['discount']['amount_off']);
        $this->assertEquals(25, $priceRange['maximum_price']['discount']['percent_off']);
    }

    /**
     * Test for checking if catalog rule price has been applied for registered customer
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/simple_product_with_tier_prices_for_logged_in_group.php
     * @magentoApiDataFixture Magento/CatalogRule/_files/catalog_rule_50_registered_customer_group.php
     */
    public function testCheckIfCatalogRuleIsAppliedForTierPriceForRegisteredCustomer(): void
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute('customer@example.com', 'password')
        );

        $this->assertNotEmpty($response['products']);
        $priceRange = $response['products']['items'][0]['price_range'];
        $this->assertEquals(10, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(5, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(5, $priceRange['minimum_price']['discount']['amount_off']);
        $this->assertEquals(50, $priceRange['minimum_price']['discount']['percent_off']);
        $this->assertEquals(10, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(5, $priceRange['maximum_price']['final_price']['value']);
        $this->assertEquals(5, $priceRange['maximum_price']['discount']['amount_off']);
        $this->assertEquals(50, $priceRange['maximum_price']['discount']['percent_off']);
    }

    /**
     * Test for checking if catalog rule price has been applied for guest
     *
     * @magentoApiDataFixture Magento/Catalog/_files/simple_product_with_tier_prices_for_not_logged_in_group.php
     * @magentoApiDataFixture Magento/CatalogRule/_files/catalog_rule_10_off_not_logged.php
     */
    public function testCheckIfCatalogRuleIsAppliedForTierPriceForGuest(): void
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery($query);

        $this->assertNotEmpty($response['products']);
        $priceRange = $response['products']['items'][0]['price_range'];
        $this->assertEquals(10, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(9, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(1, $priceRange['minimum_price']['discount']['amount_off']);
        $this->assertEquals(10, $priceRange['minimum_price']['discount']['percent_off']);
        $this->assertEquals(10, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(9, $priceRange['maximum_price']['final_price']['value']);
        $this->assertEquals(1, $priceRange['maximum_price']['discount']['amount_off']);
        $this->assertEquals(10, $priceRange['maximum_price']['discount']['percent_off']);
    }

    /**
     * Test to make sure tax amount is displayed correctly for a logged-in user
     *
     * @magentoConfigFixture default_store tax/display/type 2
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_shipping_address_36104.php
     * @magentoApiDataFixture Magento/Tax/_files/tax_rule_postal_36104.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTaxDisplayAppliesToCustomer(): void
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute('customer@example.com', 'password')
        );

        $this->assertNotEmpty($response['products']);
        $priceRange = $response['products']['items'][0]['price_range'];
        $this->assertEquals(10.75, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(10.75, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(10.75, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(10.75, $priceRange['maximum_price']['final_price']['value']);
    }

    /**
     * Test to make sure tax rule is not applied in display for guest
     *
     * @magentoConfigFixture default_store tax/display/type 2
     *
     * @magentoApiDataFixture Magento/Tax/_files/tax_rule_postal_36104.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTaxDisplayDoesNotApplyToGuest(): void
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery($query);

        $this->assertNotEmpty($response['products']);
        $priceRange = $response['products']['items'][0]['price_range'];
        $this->assertEquals(10, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(10, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(10, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(10, $priceRange['maximum_price']['final_price']['value']);
    }

    /**
     * Test to make sure tax rule is applied for customer group
     *
     * @magentoConfigFixture default_store tax/display/type 2
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer_with_group_and_address.php
     * @magentoApiDataFixture Magento/Tax/_files/tax_class_customer_group.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTaxDisplayAppliesToCustomerGroup()
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute('customer@example.com', 'password')
        );

        $this->assertNotEmpty($response['products']);
        $priceRange = $response['products']['items'][0]['price_range'];
        $this->assertEquals(10.75, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(10.75, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(10.75, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(10.75, $priceRange['maximum_price']['final_price']['value']);
    }

    /**
     * Test to make sure tax rule is not applied for a customer in the wrong group
     *
     * @magentoConfigFixture default_store tax/display/type 2
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer_with_group_and_address.php
     * @magentoApiDataFixture Magento/Customer/_files/second_customer_with_group_and_address.php
     * @magentoApiDataFixture Magento/Tax/_files/tax_class_customer_group.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTaxDisplayDoesNotApplyToWrongCustomerGroup(): void
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute('secondcustomer@example.com', 'password')
        );

        $this->assertNotEmpty($response['products']);
        $priceRange = $response['products']['items'][0]['price_range'];
        $this->assertEquals(10, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(10, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(10, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(10, $priceRange['maximum_price']['final_price']['value']);
    }

    /**
     * Test the regular price and final price display with a rounded value when there is a tax rate that match with
     * the shipping settings origin but the default tax destination calculation is not set
    */
    #[
        Config('tax/display/type', 2),
        Config('tax/cart_display/price', 2),
        Config('tax/calculation/price_includes_tax', 2),
        DataFixture(ProductTaxClass::class, as: 'product_tax_class'),
        DataFixture(
            TaxRateFixture::class,
            [
                'tax_country_id' => 'US',
                'tax_region_id' => '12',
                'tax_postcode' => '*',
                'code' => 'US-CA-*-Rate-1',
                'rate' => '7.5',
            ],
            'rate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'custom_attributes' => [
                    'tax_class_id' => '$product_tax_class.classId$'
                ],

            ],
            'product'
        )
    ]
    public function testPriceRangeValuesWithTaxApplied(): void
    {
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $query = $this->getProductSearchQuery($product->getSku());
        $response = $this->graphQlQuery($query);

        $this->assertNotEmpty($response['products']);
        $priceRange = $response['products']['items'][0]['price_range'];
        $this->assertEquals(9.3, $priceRange['minimum_price']['regular_price']['value']);
        $this->assertEquals(9.3, $priceRange['minimum_price']['final_price']['value']);
        $this->assertEquals(9.3, $priceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(9.3, $priceRange['maximum_price']['final_price']['value']);
    }

    /**
     * Get a query which user filter for product sku and returns price_tiers
     *
     * @param string $productSku
     * @return string
     */
    private function getProductSearchQuery(string $productSku): string
    {
        return <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
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
     * Get a query which user filter for product sku and returns price_range
     *
     * @param string $productSku
     * @return string
     */
    private function getProductsBySkuQuery() : string
    {
        return <<<QUERY
query getProductsBySku {
  products(filter: { sku: { eq: "configurable" } }) {
    items {
      sku
      name
      price_range {
        minimum_price {
          regular_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
          final_price {
            value
            currency
          }
        }
        maximum_price {
          regular_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
          final_price {
            value
            currency
          }
        }
      }
      ... on ConfigurableProduct {
        variants {
          product {
            sku
          }
        }
      }
    }
  }
}
QUERY;
    }
}
