<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Tests cache debug headers and cache tag validation for a simple product query
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class ProductsCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * @var GraphQl
     */
    private $graphqlController;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
    }

    /**
     * Test request is dispatched and response is checked for debug headers and cache tags
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testToCheckRequestCacheTagsForProducts(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        $product = $productRepository->get('simple1');

        $query
            = <<<QUERY
 {
           products(filter: {sku: {eq: "simple1"}})
           {
               items {
                   id
                   name
                   sku
                   description {
                   html
                   }
               }
           }
       }
QUERY;

        $request = $this->prepareRequest($query);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cat_p', 'cat_p_' . $product->getId(), 'FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * Test request is checked for debug headers and no cache tags for not existing product
     */
    public function testToCheckRequestNoTagsForProducts(): void
    {
        $query
            = <<<QUERY
            {
           products(filter: {sku: {eq: "simple10"}})
           {
               items {
                   id
                   name
                   sku
                   description {
                   html
                   }
               }
           }
       }

QUERY;
        $request = $this->prepareRequest($query);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
