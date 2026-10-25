<?php
/**
 * Copyright 2019 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Indexer\Test\Fixture\Indexer as IndexerFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;

/**
 * Test for getting canonical_url for products
 */
class ProductCanonicalUrlTest extends GraphQlAbstract
{
    #[
        Config('catalog/seo/product_canonical_tag', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(IndexerFixture::class)
    ]
    public function testProductWithCanonicalLinksMetaTagSettingsEnabled()
    {
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $productSku = $product->getSku();
        $productCanonicalUrl = $product->getUrlKey();
        $query
            = <<<QUERY
{
    products (filter: {sku: {eq: "{$productSku}"}}) {
        items {
            name
            sku
            canonical_url
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items']);

        $this->assertEquals(
            $productCanonicalUrl . '.html',
            $response['products']['items'][0]['canonical_url']
        );
        $this->assertEquals($productSku, $response['products']['items'][0]['sku']);
    }

    #[
        Config('catalog/seo/product_canonical_tag', 0),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(IndexerFixture::class)
    ]
    public function testProductWithCanonicalLinksMetaTagSettingsDisabled()
    {
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $productSku = $product->getSku();
        $query
            = <<<QUERY
{
    products (filter: {sku: {eq: "{$productSku}"}}) {
        items {
            name
            sku
            canonical_url
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertNull(
            $response['products']['items'][0]['canonical_url']
        );
        $this->assertEquals($productSku, $response['products']['items'][0]['sku']);
    }
}
