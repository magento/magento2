<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify ProductUrlPathGenerator class
 */
class ProductUrlPathGeneratorTest extends TestCase
{
    /** @var ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfig;

    /** @var CategoryUrlPathGenerator|MockObject */
    protected $categoryUrlPathGenerator;

    /** @var Product|MockObject */
    protected $product;

    /** @var ProductRepositoryInterface|MockObject */
    protected $productRepository;

    /** @var Category|MockObject */
    protected $category;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->category = $this->createMock(Category::class);
        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods(['getUrlKey'])
            ->onlyMethods(['__wakeup', 'getData', 'getName', 'formatUrlKey', 'getId', 'load', 'setStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->categoryUrlPathGenerator = $this->createMock(
            CategoryUrlPathGenerator::class
        );
        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->productRepository->expects($this->any())->method('getById')->willReturn($this->product);

        $this->productUrlPathGenerator = (new ObjectManager($this))->getObject(
            ProductUrlPathGenerator::class,
            [
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'productRepository' => $this->productRepository,
            ]
        );
    }

    /**
     * Data provider for testGetUrlPath.
     *
     * @return array
     */
    public function getUrlPathDataProvider(): array
    {
        return [
            'path based on url key uppercase' => ['Url-Key', null, 1, 'url-key'],
            'path based on url key' => ['url-key', null, 1, 'url-key'],
            'path based on product name 1' => ['', 'product-name', 1, 'product-name'],
            'path based on product name 2' => [null, 'product-name', 1, 'product-name'],
            'path based on product name 3' => [false, 'product-name', 1, 'product-name']
        ];
    }

    /**
     * Verify get url path.
     *
     * @dataProvider getUrlPathDataProvider
     * @param string|null|bool $urlKey
     * @param string|null|bool $productName
     * @param int $formatterCalled
     * @param string $result
     * @return void
     */
    public function testGetUrlPath($urlKey, $productName, $formatterCalled, $result): void
    {
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->willReturn(null);
        $this->product->expects($this->any())->method('getUrlKey')->willReturn($urlKey);
        $this->product->expects($this->any())->method('getName')->willReturn($productName);
        $this->product->expects($this->exactly($formatterCalled))
            ->method('formatUrlKey')->willReturnArgument(0);

        $this->assertEquals($result, $this->productUrlPathGenerator->getUrlPath($this->product, null));
    }

    /**
     * Verify get url key.
     *
     * @param string|bool $productUrlKey
     * @param string|bool $expectedUrlKey
     * @return void
     * @dataProvider getUrlKeyDataProvider
     */
    public function testGetUrlKey($productUrlKey, $expectedUrlKey): void
    {
        $this->product->expects($this->any())->method('getUrlKey')->willReturn($productUrlKey);
        $this->product->expects($this->any())->method('formatUrlKey')->willReturn($productUrlKey);
        $this->assertSame($expectedUrlKey, $this->productUrlPathGenerator->getUrlKey($this->product));
    }

    /**
     * Data provider for testGetUrlKey.
     *
     * @return array
     */
    public function getUrlKeyDataProvider(): array
    {
        return [
            'URL Key use default' => [false, null],
            'URL Key empty' => ['product-url', 'product-url'],
        ];
    }

    /**
     * Verify get url path with default utl key.
     *
     * @param string|null|bool $storedUrlKey
     * @param string|null|bool $productName
     * @param string $expectedUrlKey
     * @return void
     * @dataProvider getUrlPathDefaultUrlKeyDataProvider
     */
    public function testGetUrlPathDefaultUrlKey($storedUrlKey, $productName, $expectedUrlKey): void
    {
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->willReturn(null);
        $this->product->expects($this->any())->method('getUrlKey')->willReturnOnConsecutiveCalls(false, $storedUrlKey);
        $this->product->expects($this->any())->method('getName')->willReturn($productName);
        $this->product->expects($this->any())->method('formatUrlKey')->willReturnArgument(0);
        $this->assertEquals($expectedUrlKey, $this->productUrlPathGenerator->getUrlPath($this->product, null));
    }

    /**
     * Data provider for testGetUrlPathDefaultUrlKey.
     *
     * @return array
     */
    public function getUrlPathDefaultUrlKeyDataProvider(): array
    {
        return [
            ['default-store-view-url-key', null, 'default-store-view-url-key'],
            [false, 'default-store-view-product-name', 'default-store-view-product-name']
        ];
    }

    /**
     * Verify get url path with category.
     *
     * @return void
     */
    public function testGetUrlPathWithCategory(): void
    {
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->willReturn('product-path');
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')
            ->willReturn('category-url-path');

        $this->assertEquals(
            'category-url-path/product-path',
            $this->productUrlPathGenerator->getUrlPath($this->product, $this->category)
        );
    }

    /**
     * Verify get url path with suffix.
     *
     * @return void
     */
    public function testGetUrlPathWithSuffix(): void
    {
        $storeId = 1;
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->willReturn('product-path');
        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn('.html');

        $this->assertEquals(
            'product-path.html',
            $this->productUrlPathGenerator->getUrlPathWithSuffix($this->product, null)
        );
    }

    /**
     * Verify get url path with suffix and category and store.
     *
     * @return void
     */
    public function testGetUrlPathWithSuffixAndCategoryAndStore(): void
    {
        $storeId = 1;
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->willReturn('product-path');
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')
            ->willReturn('category-url-path');
        $this->storeManager->expects($this->never())->method('getStore');
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn('.html');

        $this->assertEquals(
            'category-url-path/product-path.html',
            $this->productUrlPathGenerator->getUrlPathWithSuffix($this->product, $storeId, $this->category)
        );
    }

    /**
     * Verify get canonical url path.
     *
     * @return void
     */
    public function testGetCanonicalUrlPath(): void
    {
        $this->product->expects($this->once())->method('getId')->willReturn(1);

        $this->assertEquals(
            'catalog/product/view/id/1',
            $this->productUrlPathGenerator->getCanonicalUrlPath($this->product)
        );
    }

    /**
     * Verify get canonical path with category.
     *
     * @return void
     */
    public function testGetCanonicalUrlPathWithCategory(): void
    {
        $this->product->expects($this->once())->method('getId')->willReturn(1);
        $this->category->expects($this->once())->method('getId')->willReturn(1);

        $this->assertEquals(
            'catalog/product/view/id/1/category/1',
            $this->productUrlPathGenerator->getCanonicalUrlPath($this->product, $this->category)
        );
    }
}
