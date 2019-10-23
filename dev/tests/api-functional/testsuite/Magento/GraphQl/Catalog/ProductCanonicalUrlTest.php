<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDTO;

/**
 * Test of getting URL rewrites data from products
 */
class ProductCanonicalUrlTest extends GraphQlAbstract
{
    /** @var ObjectManager $objectManager */
    private $objectManager;

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default_store catalog/seo/product_canonical_tag 1
     */
    public function testProductWithCanonicalLinksMetaTagSettingsEnabled()
    {
        $productSku = 'simple';
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

        $this->assertEquals(
            $response['products']['items'][0]['canonical_url'],
            'simple-product.html'
        );
        $this->assertEquals($response['products']['items'][0]['sku'], 'simple');
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default_store catalog/seo/product_canonical_tag 0
     */
    public function testProductWithCanonicalLinksMetaTagSettingsDisabled()
    {
        $productSku = 'simple';
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
        $this->assertEquals($response['products']['items'][0]['sku'], 'simple');
    }
}
