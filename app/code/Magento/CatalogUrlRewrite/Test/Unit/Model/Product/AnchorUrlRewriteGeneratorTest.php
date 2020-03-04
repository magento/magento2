<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AnchorUrlRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator */
    protected $anchorUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $productUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $categoryRepositoryInterface;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRegistry;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewriteFactory;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewrite;

    protected function setUp()
    {
        $this->urlRewriteFactory = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->urlRewrite = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryRepositoryInterface = $this->getMockBuilder(
            \Magento\Catalog\Api\CategoryRepositoryInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->categoryRegistry = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Model\ObjectRegistry::class)
            ->disableOriginalConstructor()->getMock();
        $this->productUrlPathGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->anchorUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'categoryRepository' => $this->categoryRepositoryInterface
            ]
        );
    }

    public function testGenerateEmpty()
    {
        $this->categoryRegistry->expects($this->any())->method('getList')->will($this->returnValue([]));

        $this->assertEquals(
            [],
            $this->anchorUrlRewriteGenerator->generate(1, $this->product, $this->categoryRegistry)
        );
    }

    public function testGenerateCategories()
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

        $this->product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($urlPathWithCategory));
        $this->productUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->will($this->returnValue($canonicalUrlPathWithCategory));
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getId')->will($this->returnValue($categoryIds));
        $category->expects($this->any())->method('getAnchorsAbove')->will($this->returnValue($categoryIds));
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
            ->withConsecutive(
                [ 'category_id' => $categoryIds[0]],
                [ 'category_id' => $categoryIds[1]],
                [ 'category_id' => $categoryIds[2]]
            )
            ->will($this->returnValue($category));
        $this->categoryRegistry->expects($this->any())->method('getList')
            ->will($this->returnValue([$category]));
        $this->urlRewrite->expects($this->any())->method('setStoreId')
            ->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setEntityId')
            ->with($productId)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setRequestPath')
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setTargetPath')
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setMetadata')
            ->will(
                $this->onConsecutiveCalls(
                    $urls[0],
                    $urls[1],
                    $urls[2]
                )
            );
        $this->urlRewriteFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->urlRewrite)
        );

        $this->assertEquals(
            $urls,
            $this->anchorUrlRewriteGenerator->generate($storeId, $this->product, $this->categoryRegistry)
        );
    }
}
