<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the product rewrite lookup of the Catalog Url Resource Model.
 */
class UrlProductRewriteLookupTest extends TestCase
{
    #[
        DbIsolation(true),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], 'product'),
    ]
    public function testGetRewriteByProductStoreReturnsRewriteWithoutCategoryPath(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $product = $fixtures->get('product');
        $category = $fixtures->get('category');
        $store = $objectManager->get(StoreManagerInterface::class)->getStore('default');
        $storeId = (int) $store->getId();
        $rootCategoryId = (int) $store->getRootCategoryId();
        $urlResource = $objectManager->create(Url::class);
        $urlResource->getConnection()->insertOnDuplicate(
            $objectManager->get(TableMaintainer::class)->getMainTable($storeId),
            [
                'category_id' => $rootCategoryId,
                'product_id' => (int) $product->getId(),
                'position' => 0,
                'is_parent' => 1,
                'store_id' => $storeId,
                'visibility' => 4,
            ]
        );
        $rewriteData = [
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::ENTITY_ID => (int) $product->getId(),
            UrlRewrite::REDIRECT_TYPE => 0,
            UrlRewrite::STORE_ID => $storeId,
        ];
        $objectManager->get(UrlPersistInterface::class)->replace([
            $objectManager->create(UrlRewrite::class, ['data' => $rewriteData + [
                UrlRewrite::REQUEST_PATH => $product->getUrlKey() . '.html',
                UrlRewrite::TARGET_PATH => 'catalog/product/view/id/' . $product->getId(),
            ]]),
            $objectManager->create(UrlRewrite::class, ['data' => $rewriteData + [
                UrlRewrite::REQUEST_PATH => $category->getUrlKey() . '/' . $product->getUrlKey() . '.html',
                UrlRewrite::TARGET_PATH => 'catalog/product/view/id/' . $product->getId()
                    . '/category/' . $category->getId(),
                UrlRewrite::METADATA => json_encode(['category_id' => (string) $category->getId()]),
            ]]),
        ]);

        $result = $urlResource->getRewriteByProductStore([(int) $product->getId() => $storeId]);

        $this->assertArrayHasKey((int) $product->getId(), $result);
        $urlRewrite = $result[(int) $product->getId()]['url_rewrite'];
        $this->assertStringNotContainsString((string) $category->getUrlKey() . '/', $urlRewrite);
        $this->assertSame((string) $product->getUrlKey() . '.html', $urlRewrite);
    }
}
