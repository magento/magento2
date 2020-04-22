<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanonicalUrlRewriteGeneratorTest extends TestCase
{
    /** @var CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var ProductUrlPathGenerator|MockObject */
    protected $productUrlPathGenerator;

    /** @var Product|MockObject */
    protected $product;

    /** @var ObjectRegistry|MockObject */
    protected $categoryRegistry;

    /** @var UrlRewriteFactory|MockObject */
    protected $urlRewriteFactory;

    /** @var UrlRewrite|MockObject */
    protected $urlRewrite;

    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryRegistry = $this->getMockBuilder(ObjectRegistry::class)
            ->disableOriginalConstructor()->getMock();
        $this->productUrlPathGenerator = $this->getMockBuilder(
            ProductUrlPathGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            CanonicalUrlRewriteGenerator::class,
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
