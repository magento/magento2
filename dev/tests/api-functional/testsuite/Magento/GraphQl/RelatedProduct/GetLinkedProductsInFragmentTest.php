<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\GraphQl\RelatedProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for get related products in fragments
 */
class GetLinkedProductsInFragmentTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_all_links.php
     */
    public function testLinkedProducts()
    {
        $productSku = 'simple_with_links';
        $query = $this->getProductQuery($productSku);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertCount(1, $response['products']['items']);
        self::assertArrayHasKey(0, $response['products']['items']);
        $item = $response['products']['items'][0];

        $linkedProductsExpectedData = $this->getLinkedProductsExpectedData();

        $this->assertLinkedProducts('related_products', $item, $linkedProductsExpectedData['related_products']);

        $this->assertLinkedProducts('upsell_products', $item, $linkedProductsExpectedData['upsell_products']);

        $this->assertLinkedProducts('crosssell_products', $item, $linkedProductsExpectedData['crosssell_products']);

    }

    /**
     * @param string $linkType
     * @param array $item
     * @param array $expectedData
     */
    private function assertLinkedProducts(string $linkType, array $item, array $expectedData): void
    {
        self::assertArrayHasKey($linkType, $item);
        $linkedProducts = $item[$linkType];
        self::assertCount(1, $linkedProducts);
        $linkedProduct = $linkedProducts[0];
        self::assertArrayHasKey('sku', $linkedProduct);
        self::assertArrayHasKey('name', $linkedProduct);
        self::assertArrayHasKey('url_key', $linkedProduct);
        self::assertEquals($linkedProduct['sku'], $expectedData['sku']);
        self::assertEquals($linkedProduct['name'], $expectedData['name']);
        self::assertEquals($linkedProduct['url_key'], $expectedData['url_key']);
        self::assertArrayHasKey('price_range', $linkedProduct);

        self::assertArrayHasKey('minimum_price', $linkedProduct['price_range']);
        self::assertArrayHasKey('final_price', $linkedProduct['price_range']['minimum_price']);
        self::assertArrayHasKey('value', $linkedProduct['price_range']['minimum_price']['final_price']);
        $minimumPrice = $linkedProduct['price_range']['minimum_price']['final_price']['value'];
        self::assertEquals($minimumPrice, $expectedData['min_price']);

        self::assertArrayHasKey('maximum_price', $linkedProduct['price_range']);
        self::assertArrayHasKey('final_price', $linkedProduct['price_range']['maximum_price']);
        self::assertArrayHasKey('value', $linkedProduct['price_range']['maximum_price']['final_price']);
        $maximumPrice = $linkedProduct['price_range']['maximum_price']['final_price']['value'];
        self::assertEquals($maximumPrice, $expectedData['max_price']);
    }

    /**
     * Return linked products expected data
     *
     * @return array
     */
    private function getLinkedProductsExpectedData(): array
    {
        $expectedData = [
            'related_products' => [
                'name' => 'Simple Related Product',
                'sku' => 'simple_related',
                'url_key' => 'simple-related-product',
                'min_price' => 10,
                'max_price' => 10,
            ],
            'upsell_products' => [
                'name' => 'Simple UpSell Product',
                'sku' => 'simple_up',
                'url_key' => 'simple-upsell-product',
                'min_price' => 10,
                'max_price' => 10,
            ],
            'crosssell_products' => [
                'name' => 'Simple CrossSell Product',
                'sku' => 'simple_cross',
                'url_key' => 'simple-crosssell-product',
                'min_price' => 10,
                'max_price' => 10,
            ],
        ];
        return $expectedData;
    }

    /**
     * Returns GraphQL query for retrieving a product with it's links
     *
     * @param string $sku
     * @return string
     */
    private function getProductQuery(string $sku): string
    {
        return <<<QUERY
query {
  products(search: "$sku") {
    items {
        ...ProductFragment
        related_products {
          ...ProductFragment
        }
        upsell_products {
          ...ProductFragment
        }
        crosssell_products {
          ...ProductFragment
        }
    }
  }
}
fragment ProductFragment on ProductInterface {
    sku
    name
    url_key
    price_range {
      minimum_price {
        final_price {
          value
        }
      }
      maximum_price {
        final_price {
          value
        }
      }
    }
  }
QUERY;
    }

}
