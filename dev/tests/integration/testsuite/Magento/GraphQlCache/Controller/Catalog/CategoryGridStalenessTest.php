<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that removing a category product does not leave stale GraphQL grid data.
 *
 * @magentoAppArea graphql
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Catalog/_files/category_product.php
 */
class CategoryGridStalenessTest extends TestCase
{
    public function testRemovedProductIsNotReturnedByCachedCategoryQuery(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(GraphQlRequest::class);
        $objectManager->get(IndexerRegistry::class)->get('catalog_category_product')->reindexAll();
        $query = <<<'GRAPHQL'
{
  category(id: 333) {
    products(pageSize: 20, currentPage: 1) { items { sku } }
  }
}
GRAPHQL;

        $first = $request->send($query);
        $this->assertStringContainsString('simple333', (string) $first->getBody());

        $product = $objectManager->get(CollectionFactory::class)->create()
            ->addAttributeToFilter('sku', 'simple333')
            ->getFirstItem();
        $this->assertNotEmpty($product->getId());
        $originalStatus = $product->getStatus();
        try {
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
            $objectManager->get(ProductResource::class)->save($product);
            $objectManager->get(IndexerRegistry::class)->get('catalog_category_product')->reindexAll();

            $second = $request->send($query);
            $this->assertStringNotContainsString('simple333', (string) $second->getBody());
        } finally {
            $product->setStatus($originalStatus);
            $objectManager->get(ProductResource::class)->save($product);
            $objectManager->get(IndexerRegistry::class)->get('catalog_category_product')->reindexAll();
        }
    }
}
