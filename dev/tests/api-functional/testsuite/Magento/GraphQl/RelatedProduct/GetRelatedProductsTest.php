<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\RelatedProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for get related products
 */
class GetRelatedProductsTest extends GraphQlAbstract
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
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertCount(1, $response['products']['items']);
        self::assertArrayHasKey(0, $response['products']['items']);
        self::assertArrayHasKey('related_products', $response['products']['items'][0]);
        $relatedProducts = $response['products']['items'][0]['related_products'];
        self::assertCount(2, $relatedProducts);
        self::assertRelatedProducts($relatedProducts);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related_disabled.php
     */
    public function testQueryDisableRelatedProduct()
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
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertCount(1, $response['products']['items']);
        self::assertArrayHasKey(0, $response['products']['items']);
        self::assertArrayHasKey('related_products', $response['products']['items'][0]);
        $relatedProducts = $response['products']['items'][0]['related_products'];
        self::assertCount(0, $relatedProducts);
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
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertCount(1, $response['products']['items']);
        self::assertArrayHasKey(0, $response['products']['items']);
        self::assertArrayHasKey('crosssell_products', $response['products']['items'][0]);
        $crossSellProducts = $response['products']['items'][0]['crosssell_products'];
        self::assertCount(1, $crossSellProducts);
        $crossSellProduct = $crossSellProducts[0];
        self::assertArrayHasKey('sku', $crossSellProduct);
        self::assertArrayHasKey('name', $crossSellProduct);
        self::assertArrayHasKey('url_key', $crossSellProduct);
        self::assertEquals($crossSellProduct['sku'], 'simple');
        self::assertEquals($crossSellProduct['name'], 'Simple Cross Sell');
        self::assertEquals($crossSellProduct['url_key'], 'simple-cross-sell');
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
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertCount(1, $response['products']['items']);
        self::assertArrayHasKey(0, $response['products']['items']);
        self::assertArrayHasKey('upsell_products', $response['products']['items'][0]);
        $upSellProducts = $response['products']['items'][0]['upsell_products'];
        self::assertCount(1, $upSellProducts);
        $upSellProduct = $upSellProducts[0];
        self::assertArrayHasKey('sku', $upSellProduct);
        self::assertArrayHasKey('name', $upSellProduct);
        self::assertArrayHasKey('url_key', $upSellProduct);
        self::assertEquals($upSellProduct['sku'], 'simple');
        self::assertEquals($upSellProduct['name'], 'Simple Up Sell');
        self::assertEquals($upSellProduct['url_key'], 'simple-up-sell');
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
            self::assertArrayHasKey('sku', $product);
            self::assertArrayHasKey('name', $product);
            self::assertArrayHasKey('url_key', $product);

            self::assertArrayHasKey($product['sku'], $expectedData);
            $productExpectedData = $expectedData[$product['sku']];

            self::assertEquals($product['name'], $productExpectedData['name']);
            self::assertEquals($product['url_key'], $productExpectedData['url_key']);
        }
    }
}
