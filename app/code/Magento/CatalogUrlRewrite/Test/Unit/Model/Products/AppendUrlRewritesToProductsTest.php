<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Products;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\Product\GetProductUrlRewriteDataByStore;
use Magento\CatalogUrlRewrite\Model\Products\AppendUrlRewritesToProducts;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AppendUrlRewritesToProductsTest extends TestCase
{
    /** @var ProductUrlRewriteGenerator|MockObject */
    private ProductUrlRewriteGenerator|MockObject $generator;

    /** @var GetProductUrlRewriteDataByStore|MockObject */
    private GetProductUrlRewriteDataByStore|MockObject $storeData;

    /** @var UrlPersistInterface|MockObject */
    private UrlPersistInterface|MockObject $urlPersist;

    /** @var AppendUrlRewritesToProducts */
    private AppendUrlRewritesToProducts $appendRewrites;

    /** @var ScopeConfigInterface|MockObject */
    private ScopeConfigInterface|MockObject $scopeConfig;

    protected function setUp(): void
    {
        $this->generator = $this->createMock(ProductUrlRewriteGenerator::class);
        $this->storeData = $this->createMock(GetProductUrlRewriteDataByStore::class);
        $this->urlPersist = $this->createMock(UrlPersistInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $pathGenerator = $this->createStub(ProductUrlPathGenerator::class);
        $pathGenerator->method('getUrlPath')->willReturnCallback(
            static fn (Product $product): string => $product->getUrlKey()
        );
        $this->appendRewrites = new AppendUrlRewritesToProducts(
            $this->generator,
            $this->createStub(StoreViewService::class),
            $pathGenerator,
            $this->urlPersist,
            $this->storeData,
            $this->scopeConfig
        );
    }

    /**
     * Category assignments must reach both inherited and overridden URL keys.
     *
     * @param int $storeId
     * @param array $categoryIds
     * @param bool $saveHistory
     * @return void
     */
    #[DataProvider('categoryChangeProvider')]
    public function testCategoryChangesUseEachStoreData(int $storeId, array $categoryIds, bool $saveHistory): void
    {
        $product = $this->createProduct($storeId);
        $product->setCategoryIds($categoryIds);
        $product->setData('is_changed_categories', true);
        $product->setData('save_rewrites_history', $saveHistory);
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(ProductScopeRewriteGenerator::URL_REWRITE_SCOPE_CONFIG_PATH)
            ->willReturn(ProductScopeRewriteGenerator::STORE_VIEW_URL_REWRITE_SCOPE);
        $otherStoreId = $storeId === 1 ? 2 : 1;
        $otherUrlKey = $otherStoreId === 1 ? 'default-key' : 'custom-key';
        $this->storeData->expects($this->once())->method('execute')
            ->with($this->isInstanceOf(Product::class), $otherStoreId)
            ->willReturn(['url_key' => $otherUrlKey, 'visibility' => Visibility::VISIBILITY_IN_CATALOG]);
        $this->storeData->expects($this->once())->method('clearProductUrlRewriteDataCache')->with($product);
        $generated = [];
        $this->generator->expects($this->exactly(2))->method('generate')->willReturnCallback(
            static function (Product $scopedProduct) use (&$generated, $storeId, $saveHistory): array {
                $generated[$scopedProduct->getStoreId()] = [
                    $scopedProduct->getUrlKey(),
                    $scopedProduct->getUrlPath(),
                    $scopedProduct->getVisibility(),
                    $scopedProduct->getCategoryIds(),
                    $scopedProduct->getData('save_rewrites_history'),
                ];
                if ($scopedProduct->getStoreId() !== $storeId) {
                    // The regenerator can replace this transient flag with the other store's configuration.
                    $scopedProduct->setData('save_rewrites_history', !$saveHistory);
                }
                return ['rewrite-' . $scopedProduct->getStoreId()];
            }
        );
        $this->urlPersist->expects($this->once())->method('replace')->with($this->callback(
            static function (array $rewrites): bool {
                sort($rewrites);
                return $rewrites === ['rewrite-1', 'rewrite-2'];
            }
        ));

        $this->appendRewrites->execute([$product], []);

        $this->assertSame(
            [$otherUrlKey, $otherUrlKey, Visibility::VISIBILITY_IN_CATALOG, $categoryIds, $saveHistory],
            $generated[$otherStoreId]
        );
        $this->assertSame(
            ['saved-key', 'saved-key', Visibility::VISIBILITY_BOTH, $categoryIds, $saveHistory],
            $generated[$storeId]
        );
        $this->assertSame($storeId, $product->getStoreId());
        $this->assertSame('saved-key', $product->getUrlPath());
        $this->assertSame($saveHistory, $product->getData('save_rewrites_history'));
    }

    /**
     * @return array
     */
    public static function categoryChangeProvider(): array
    {
        return [
            'add with history' => [1, [3, 4], true],
            'remove with history' => [1, [], true],
            'inherited other store' => [2, [3, 4], true],
            'history disabled' => [1, [3, 4], false],
        ];
    }

    /**
     * Global saves and saves without category changes retain the existing dispatch.
     *
     * @param int $storeId
     * @param bool $changedCategories
     * @param string $rewriteScope
     * @return void
     */
    #[DataProvider('unchangedScopeProvider')]
    public function testSavesRetainTheirScope(
        int $storeId,
        bool $changedCategories,
        string $rewriteScope
    ): void {
        $product = $this->createProduct($storeId);
        $product->setData('is_changed_categories', $changedCategories);
        $this->scopeConfig->method('getValue')->willReturn($rewriteScope);
        $this->storeData->expects($this->never())->method('execute');
        $this->storeData->expects($this->once())->method('clearProductUrlRewriteDataCache')->with($product);
        $this->generator->expects($this->once())->method('generate')->with($product)->willReturn(['current']);
        $this->urlPersist->expects($this->once())->method('replace')->with(['current']);

        $this->appendRewrites->execute([$product], []);
    }

    /**
     * @return array
     */
    public static function unchangedScopeProvider(): array
    {
        return [
            'store view URL key edit' => [1, false, ProductScopeRewriteGenerator::STORE_VIEW_URL_REWRITE_SCOPE],
            'global category edit' => [0, true, ProductScopeRewriteGenerator::STORE_VIEW_URL_REWRITE_SCOPE],
            'website rewrite context' => [1, true, ProductScopeRewriteGenerator::WEBSITE_URL_REWRITE_SCOPE],
        ];
    }

    /**
     * @param int $storeId
     * @return Product
     */
    private function createProduct(int $storeId): Product
    {
        $product = $this->getMockBuilder(Product::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $product->setData([
            'store_id' => $storeId,
            'store_ids' => [1, 2],
            'url_key' => 'saved-key',
            'url_path' => 'old-key',
            'visibility' => Visibility::VISIBILITY_BOTH,
            'category_ids' => [3],
        ]);
        return $product;
    }
}
