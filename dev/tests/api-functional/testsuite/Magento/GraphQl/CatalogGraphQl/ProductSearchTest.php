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
 * Test class to verify product search, used for GraphQL resolver
 * for configurable product returns only visible products.
 */
class ProductSearchTest extends GraphQlAbstract
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
     * Test for checking if graphQL query fpr configurable product returns
     * expected visible items
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_different_super_attribute.php
     */
    public function testCheckIfConfigurableProductVisibilityReturnsExpectedItem(): void
    {
        $productName = 'Configurable Product';
        $productSku = 'configurable';
        $query = $this->getProductSearchQuery($productName, $productSku);

        $response = $this->graphQlQuery($query);

        $this->assertNotEmpty($response['products']);
        $this->assertEquals(1, $response['products']['total_count']);
        $this->assertNotEmpty($response['products']['items']);
        $this->assertEquals($productName, $response['products']['items'][0]['name']);
        $this->assertEquals($productSku, $response['products']['items'][0]['sku']);
    }

    /**
     * Get a query which user filter for product sku and search by product name
     *
     * @param string $productName
     * @param string $productSku
     * @return string
     */
    private function getProductSearchQuery(string $productName, string $productSku): string
    {
        return <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}, search: "$productName", sort: {}, pageSize: 200, currentPage: 1) {
    total_count
    page_info {
        total_pages
        current_page
        page_size
    }
    items {
        name
        sku
    }
  }
}
QUERY;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     */
    public function testSearchSuggestions() : void
    {
        $query = $this->getSearchQueryWithSuggestions();
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']);
        $this->assertEmpty($response['products']['items']);
        $this->assertNotEmpty($response['products']['suggestions']);
    }

    /**
     * Prepare search query with suggestions
     *
     * @return string
     */
    private function getSearchQueryWithSuggestions() : string
    {
        return <<<QUERY
{
  products(
    search: "smiple"
   ) {
    items {
      name
      sku
    }
    suggestions {
      search
    }
  }
}
QUERY;
    }
}
