<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQl;

use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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
        $this->assertEquals(10.75, round($priceRange['minimum_price']['regular_price']['value'], 2));
        $this->assertEquals(10.75, round($priceRange['minimum_price']['final_price']['value'], 2));
        $this->assertEquals(10.75, round($priceRange['maximum_price']['regular_price']['value'], 2));
        $this->assertEquals(10.75, round($priceRange['maximum_price']['final_price']['value'], 2));
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
        $this->assertEquals(10.75, round($priceRange['minimum_price']['regular_price']['value'], 2));
        $this->assertEquals(10.75, round($priceRange['minimum_price']['final_price']['value'], 2));
        $this->assertEquals(10.75, round($priceRange['maximum_price']['regular_price']['value'], 2));
        $this->assertEquals(10.75, round($priceRange['maximum_price']['final_price']['value'], 2));
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
}
