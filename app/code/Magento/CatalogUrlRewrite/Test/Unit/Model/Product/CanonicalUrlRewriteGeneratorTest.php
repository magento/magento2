<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CanonicalUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $productUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

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
        $this->categoryRegistry = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Model\ObjectRegistry::class)
            ->disableOriginalConstructor()->getMock();
        $this->productUrlPathGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'urlRewriteFactory' => $this->urlRewriteFactory
            ]
        );
    }

    public function testGenerate()
    {
        $requestPath = 'simple-product.html';
        $storeId = 10;
        $productId = 'product_id';
        $targetPath = 'catalog/product/view/id/' . $productId;

        $this->product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($requestPath));
        $this->productUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->will($this->returnValue($targetPath));
        $this->categoryRegistry->expects($this->any())->method('getList')->will($this->returnValue([]));

        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setEntityId')->with($productId)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->with($requestPath)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->with($targetPath)
            ->will($this->returnSelf());
        $this->urlRewriteFactory->expects($this->any())->method('create')->will($this->returnValue($this->urlRewrite));
        $this->assertEquals(
            [
                $this->urlRewrite,
            ],
            $this->canonicalUrlRewriteGenerator->generate($storeId, $this->product)
        );
    }
}
