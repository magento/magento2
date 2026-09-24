<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Verifies the built-in full page cache real flow: a storefront GraphQL request is served from FPC on
 * the second call (HIT) and is invalidated by tag when the product is saved (MISS + fresh content).
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 */
class ProductPageCacheInvalidationTest extends AbstractGraphqlCacheTest
{
    /**
     * @magentoCache full_page enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testProductPageIsCachedThenInvalidatedOnSave(): void
    {
        $query = '{ products(filter: { sku: { eq: "simple1" } }) { items { sku name } } }';
        $repository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $repository->get('simple1', false, null, true);
        $originalName = $product->getName();
        $updatedName = $originalName . ' Updated';

        try {
            $first = $this->dispatchGraphQlGETRequest(['query' => $query]);
            $this->assertEquals('MISS', $first->getHeader('X-Magento-Cache-Debug')->getFieldValue());
            $this->assertStringContainsString($originalName, (string) $first->getBody());

            $second = $this->dispatchGraphQlGETRequest(['query' => $query]);
            $this->assertEquals('HIT', $second->getHeader('X-Magento-Cache-Debug')->getFieldValue());

            $product->setName($updatedName);
            $repository->save($product);

            // Saving the product must invalidate the FPC entry by its cache tag.
            $third = $this->dispatchGraphQlGETRequest(['query' => $query]);
            $this->assertEquals('MISS', $third->getHeader('X-Magento-Cache-Debug')->getFieldValue());
            $this->assertStringContainsString($updatedName, (string) $third->getBody());
        } finally {
            $product->setName($originalName);
            $repository->save($product);
        }
    }
}
