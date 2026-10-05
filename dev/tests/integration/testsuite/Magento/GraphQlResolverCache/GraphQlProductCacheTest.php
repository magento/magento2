<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache;

use Magento\GraphQl\Service\GraphQlRequest;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies product resolver caching through a real GraphQL request.
 *
 * @magentoAppArea graphql
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
 */
class GraphQlProductCacheTest extends TestCase
{
    public function testProductQueryCanBeServedRepeatedlyFromResolverCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(GraphQlRequest::class);
        $cache = $objectManager->get(Type::class);
        $cache->clean();

        $query = <<<'GRAPHQL'
{
  products(filter: { sku: { eq: "simple_product" } }) {
    items { sku name }
  }
}
GRAPHQL;

        try {
            $first = $request->send($query);
            $this->assertSame(200, $first->getHttpResponseCode());
            $this->assertStringNotContainsString('errors', strtolower((string) $first->getBody()));
            $this->assertStringContainsString('simple_product', (string) $first->getBody());

            $second = $request->send($query);
            $this->assertSame(200, $second->getHttpResponseCode());
            $this->assertSame((string) $first->getBody(), (string) $second->getBody());
        } finally {
            $cache->clean();
        }
    }

    public function testProductUpdateInvalidatesResolverCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(GraphQlRequest::class);
        $cache = $objectManager->get(Type::class);
        $repository = $objectManager->get(ProductRepositoryInterface::class);
        $cache->clean();
        $query = '{ products(filter: { sku: { eq: "simple_product" } }) { items { sku name } } }';

        try {
            $first = $request->send($query);
            $this->assertStringContainsString('simple_product', (string) $first->getBody());

            $product = $repository->get('simple_product', false, null, true);
            $originalName = $product->getName();
            $product->setName($originalName . ' Updated');
            $repository->save($product);

            $updated = $request->send($query);
            $this->assertStringContainsString($originalName . ' Updated', (string) $updated->getBody());
        } finally {
            if (isset($product, $originalName)) {
                $product->setName($originalName);
                $repository->save($product);
            }
            $cache->clean();
        }
    }
}
