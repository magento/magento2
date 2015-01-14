<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\TestFramework\Helper\ObjectManager;

class CategoriesUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator */
    protected $categoriesUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $productUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRegistry;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewriteBuilder;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewrite;

    protected function setUp()
    {
        $this->urlRewriteBuilder = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder')
            ->disableOriginalConstructor()->getMock();
        $this->urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()->getMock();
        $this->categoryRegistry = $this->getMockBuilder('\Magento\CatalogUrlRewrite\Model\ObjectRegistry')
            ->disableOriginalConstructor()->getMock();
        $this->productUrlPathGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->categoriesUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator',
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'urlRewriteBuilder' => $this->urlRewriteBuilder
            ]
        );
    }

    public function testGenerateEmpty()
    {
        $this->categoryRegistry->expects($this->any())->method('getList')->will($this->returnValue([]));

        $this->assertEquals(
            [],
            $this->categoriesUrlRewriteGenerator->generate(1, $this->product, $this->categoryRegistry)
        );
    }

    public function testGenerateCategories()
    {
        $urlPathWithCategory = 'category/simple-product.html';
        $storeId = 10;
        $productId = 'product_id';
        $canonicalUrlPathWithCategory = 'canonical-path-with-category';
        $categoryId = 'category_id';

        $this->product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($urlPathWithCategory));
        $this->productUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->will($this->returnValue($canonicalUrlPathWithCategory));
        $category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $category->expects($this->any())->method('getId')->will($this->returnValue($categoryId));
        $this->categoryRegistry->expects($this->any())->method('getList')
            ->will($this->returnValue([$category]));

        $this->urlRewriteBuilder->expects($this->any())->method('setStoreId')->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setEntityId')->with($productId)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setRequestPath')->with($urlPathWithCategory)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setTargetPath')->with($canonicalUrlPathWithCategory)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setMetadata')
            ->with(['category_id' => $categoryId])->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('create')->will($this->returnValue($this->urlRewrite));

        $this->assertEquals(
            [
                $this->urlRewrite,
            ],
            $this->categoriesUrlRewriteGenerator->generate($storeId, $this->product, $this->categoryRegistry)
        );
    }
}
