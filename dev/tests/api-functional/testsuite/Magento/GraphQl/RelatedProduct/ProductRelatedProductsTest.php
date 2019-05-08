<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\RelatedProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductRelatedProductsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related_multiple.php
     */
    public function testQueryRelatedProducts()
    {
        $productSku = 'simple_with_cross';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {            
            related_products
            {
                sku
                name
                url_key
                id
                created_at
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('related_products', $response['products']['items'][0]);
        $relatedProducts = $response['products']['items'][0]['related_products'];
        $this->assertCount(2, $relatedProducts);
        $this->assertRelatedProducts($relatedProducts);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_crosssell.php
     */
    public function testQueryCrossSellProducts()
    {
        $productSku = 'simple_with_cross';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {            
            crosssell_products
            {
                sku
                name
                url_key
                id
                created_at
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('crosssell_products', $response['products']['items'][0]);
        $crossSellProducts = $response['products']['items'][0]['crosssell_products'];
        $this->assertCount(1, $crossSellProducts);
        $crossSellProduct = $crossSellProducts[0];
        $this->assertArrayHasKey('sku', $crossSellProduct);
        $this->assertArrayHasKey('name', $crossSellProduct);
        $this->assertArrayHasKey('url_key', $crossSellProduct);
        $this->assertArrayHasKey('id', $crossSellProduct);
        $this->assertArrayHasKey('created_at', $crossSellProduct);
        $this->assertEquals($crossSellProduct['sku'], 'simple');
        $this->assertEquals($crossSellProduct['name'], 'Simple Cross Sell');
        $this->assertEquals($crossSellProduct['url_key'], 'simple-cross-sell');
        $this->assertNotEmpty($crossSellProduct['created_at']);
        $this->assertNotEmpty($crossSellProduct['id']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_upsell.php
     */
    public function testQueryUpSellProducts()
    {
        $productSku = 'simple_with_upsell';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {            
            upsell_products
            {
                sku
                name
                url_key
                id
                created_at
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('upsell_products', $response['products']['items'][0]);
        $upSellProducts = $response['products']['items'][0]['upsell_products'];
        $this->assertCount(1, $upSellProducts);
        $upSellProduct = $upSellProducts[0];
        $this->assertArrayHasKey('sku', $upSellProduct);
        $this->assertArrayHasKey('name', $upSellProduct);
        $this->assertArrayHasKey('url_key', $upSellProduct);
        $this->assertArrayHasKey('id', $upSellProduct);
        $this->assertArrayHasKey('created_at', $upSellProduct);
        $this->assertEquals($upSellProduct['sku'], 'simple');
        $this->assertEquals($upSellProduct['name'], 'Simple Up Sell');
        $this->assertEquals($upSellProduct['url_key'], 'simple-up-sell');
        $this->assertNotEmpty($upSellProduct['created_at']);
        $this->assertNotEmpty($upSellProduct['id']);
    }

    /**
     * @param array $relatedProducts
     */
    private function assertRelatedProducts(array $relatedProducts): void
    {
        $expectedData = [
            'simple' => [
                'name' => 'Simple Related Product',
                'url_key' => 'simple-related-product',

            ],
            'simple_with_cross_two' => [
                'name' => 'Simple Product With Related Product Two',
                'url_key' => 'simple-product-with-related-product-two',
            ]
        ];

        foreach ($relatedProducts as $product) {
            $this->assertArrayHasKey('sku', $product);
            $this->assertArrayHasKey('name', $product);
            $this->assertArrayHasKey('url_key', $product);
            $this->assertArrayHasKey('id', $product);
            $this->assertArrayHasKey('created_at', $product);

            $this->assertArrayHasKey($product['sku'], $expectedData);
            $productExpectedData = $expectedData[$product['sku']];

            $this->assertEquals($product['name'], $productExpectedData['name']);
            $this->assertEquals($product['url_key'], $productExpectedData['url_key']);
            $this->assertNotEmpty($product['created_at']);
            $this->assertNotEmpty($product['id']);
        }
    }
}
