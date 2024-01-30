<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AnchorUrlRewriteGeneratorTest extends TestCase
{
    /** @var AnchorUrlRewriteGenerator */
    protected $anchorUrlRewriteGenerator;

    /** @var ProductUrlPathGenerator|MockObject */
    protected $productUrlPathGenerator;

    /** @var Product|MockObject */
    protected $product;

    /** @var CategoryRepositoryInterface|MockObject */
    private $categoryRepositoryInterface;

    /** @var ObjectRegistry|MockObject */
    protected $categoryRegistry;

    /** @var UrlRewriteFactory|MockObject */
    protected $urlRewriteFactory;

    /** @var UrlRewrite|MockObject */
    protected $urlRewrite;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(UrlRewriteFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryRepositoryInterface = $this->getMockBuilder(
            CategoryRepositoryInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->categoryRegistry = $this->getMockBuilder(ObjectRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlPathGenerator = $this->getMockBuilder(
            ProductUrlPathGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->anchorUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            AnchorUrlRewriteGenerator::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'categoryRepository' => $this->categoryRepositoryInterface
            ]
        );
    }

    /**
     * Verify generate if category registry list is empty.
     *
     * @return void
     */
    public function testGenerateEmpty(): void
    {
        $this->categoryRegistry->expects($this->any())->method('getList')->willReturn([]);

        $this->assertEquals(
            [],
            $this->anchorUrlRewriteGenerator->generate(1, $this->product, $this->categoryRegistry)
        );
    }

    /**
     * Verify generate product rewrites for anchor categories.
     *
     * @return void
     */
    public function testGenerateCategories(): void
    {
        $urlPathWithCategory = 'category1/category2/category3/simple-product.html';
        $storeId = 10;
        $productId = 12;
        $canonicalUrlPathWithCategory = 'canonical-path-with-category';
        $categoryParentId = '1';
        $categoryIds = [$categoryParentId,'2','3','4'];
        $urls = ['category1/simple-product.html',
            'category1/category2/simple-product.html',
            'category1/category2/category3/simple-product.html'];

        $this->product->expects($this->any())->method('getId')->willReturn($productId);
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->willReturn($urlPathWithCategory);
        $this->productUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->willReturn($canonicalUrlPathWithCategory);
        $category = $this->createMock(Category::class);
        $category->expects($this->any())->method('getId')->willReturn($categoryIds);
        $category->expects($this->any())->method('getAnchorsAbove')->willReturn($categoryIds);
        $category->expects($this->any())->method('getParentId')->will(
            $this->onConsecutiveCalls(
                $categoryIds[0],
                $categoryIds[1],
                $categoryIds[2],
                $categoryIds[3]
            )
        );
        $this->categoryRepositoryInterface
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($categoryIds, $storeId) use ($category) {
                if ($categoryIds[0] || $categoryIds[1] || $categoryIds[2] && $storeId) {
                    return $category;
                }
            });

        $this->categoryRegistry->expects($this->any())->method('getList')
            ->willReturn([$category]);
        $this->urlRewrite->expects($this->any())->method('setStoreId')
            ->with($storeId)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityId')
            ->with($productId)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setMetadata')
            ->will(
                $this->onConsecutiveCalls(
                    $urls[0],
                    $urls[1],
                    $urls[2]
                )
            );
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn(
            $this->urlRewrite
        );

        $this->assertEquals(
            $urls,
            $this->anchorUrlRewriteGenerator->generate($storeId, $this->product, $this->categoryRegistry)
        );
    }
}
