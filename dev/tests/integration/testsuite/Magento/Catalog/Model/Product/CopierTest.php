<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Model\Store;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite as UrlRewriteFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests product copier.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CopierTest extends TestCase
{
    /**
     * Tests copying of product.
     *
     * Case when url_key is set for store view and has equal value to default store.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore_with_url_key.php
     * @magentoAppArea adminhtml
     */
    public function testProductCopyWithExistingUrlKey()
    {
        $productSKU = 'simple_100';
        /** @var ProductRepository $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepository::class);
        $copier = Bootstrap::getObjectManager()->get(Copier::class);

        $product = $productRepository->get($productSKU);
        $duplicate = $copier->copy($product);

        $duplicateStoreView = $productRepository->getById($duplicate->getId(), false, Store::DISTRO_STORE_ID);
        $productStoreView = $productRepository->get($productSKU, false, Store::DISTRO_STORE_ID);

        $this->assertNotEquals(
            $duplicateStoreView->getUrlKey(),
            $productStoreView->getUrlKey(),
            'url_key of product duplicate should be different then url_key of the product for the same store view'
        );
    }

    /**
     * @param string $entityType
     * @param int $redirectType
     * @return void
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(true),
        DataFixture(ProductFixture::class, ['sku' => 'copier-rewrite-src', 'url_key' => 'copier-rewrite-src']),
        DataProvider('takenRequestPathDataProvider'),
    ]
    public function testCopySkipsUrlKeyTakenByUrlRewrite(string $entityType, int $redirectType): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $productResource = $objectManager->get(ProductResource::class);
        $source = $productRepository->get('copier-rewrite-src', true, Store::DEFAULT_STORE_ID, true);
        $source->setUrlPath('copier-rewrite-src');
        $productResource->saveAttribute($source, 'url_path');
        $objectManager->get(UrlRewriteFixture::class)->apply(
            [
                'entity_type' => $entityType,
                'entity_id' => 0,
                'request_path' => 'copier-rewrite-src-1.html',
                'target_path' => 'copier-rewrite-target.html',
                'redirect_type' => $redirectType,
                'store_id' => Store::DISTRO_STORE_ID,
            ]
        );
        $source = $productRepository->get('copier-rewrite-src', true, Store::DEFAULT_STORE_ID, true);

        $duplicate = $objectManager->get(Copier::class)->copy($source);

        $this->assertSame(Store::DEFAULT_STORE_ID, (int)$duplicate->getStoreId());
        $this->assertSame(
            'copier-rewrite-src-2',
            $productRepository->getById($duplicate->getId(), false, Store::DEFAULT_STORE_ID, true)->getUrlKey()
        );
        $this->assertSame(
            'copier-rewrite-src',
            $productRepository->get('copier-rewrite-src', false, Store::DEFAULT_STORE_ID, true)->getUrlKey()
        );
    }

    /**
     * @return array
     */
    public static function takenRequestPathDataProvider(): array
    {
        return [
            'custom redirect' => ['custom', 301],
            'historical product redirect' => ['product', 301],
            'category path' => ['category', 0],
            'cms page' => ['cms-page', 0],
        ];
    }

    /**
     * @return void
     */
    #[
        AppArea('adminhtml'),
        AppIsolation(true),
        DbIsolation(true),
        DataFixtureBeforeTransaction(StoreFixture::class, ['store_group_id' => 1, 'website_id' => 1]),
        DataFixture(ProductFixture::class, ['sku' => 'copier-store-src', 'url_key' => 'copier-store-src']),
        DataFixture(
            UrlRewriteFixture::class,
            [
                'request_path' => 'copier-store-view-1.html',
                'target_path' => 'copier-store-target.html',
                'redirect_type' => 301,
                'store_id' => Store::DISTRO_STORE_ID,
            ]
        ),
    ]
    public function testCopySkipsStoreUrlKeyTakenByUrlRewrite(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $productResource = $objectManager->get(ProductResource::class);
        $storeSource = $productRepository->get('copier-store-src', true, Store::DISTRO_STORE_ID, true);
        $storeSource->setStoreId(Store::DISTRO_STORE_ID);
        $storeSource->setUrlKey('copier-store-view');
        $productResource->saveAttribute($storeSource, 'url_key');
        $source = $productRepository->get('copier-store-src', true, Store::DEFAULT_STORE_ID, true);
        $this->assertSame('copier-store-src', $source->getUrlKey());

        $duplicate = $objectManager->get(Copier::class)->copy($source);

        $this->assertSame(Store::DEFAULT_STORE_ID, (int)$duplicate->getStoreId());
        $this->assertSame(
            'copier-store-src-1',
            $productRepository->getById($duplicate->getId(), false, Store::DEFAULT_STORE_ID, true)->getUrlKey()
        );
        $this->assertSame(
            'copier-store-view-2',
            $productRepository->getById($duplicate->getId(), false, Store::DISTRO_STORE_ID, true)->getUrlKey()
        );
        $this->assertSame(
            'copier-store-view',
            $productRepository->get('copier-store-src', false, Store::DISTRO_STORE_ID, true)->getUrlKey()
        );
    }
}
