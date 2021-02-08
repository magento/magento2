<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductUrlRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $canonicalUrlRewriteGenerator;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $currentUrlRewritesRegenerator;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $categoriesUrlRewriteGenerator;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $anchorUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit\Framework\MockObject\MockObject */
    protected $storeViewService;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject */
    protected $product;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $objectRegistryFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $storeManager;

    /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection|\PHPUnit\Framework\MockObject\MockObject */
    protected $categoriesCollection;

    /** @var \PHPUnit\Framework\MockObject\MockObject  */
    private $productScopeRewriteGenerator;

    /**
     * Test method
     */
    protected function setUp(): void
    {
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->categoriesCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class
        )
            ->disableOriginalConstructor()->getMock();
        $this->product->expects($this->any())->method('getCategoryCollection')
            ->willReturn($this->categoriesCollection);
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->currentUrlRewritesRegenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->categoriesUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->anchorUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->objectRegistryFactory = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->storeViewService = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class)
            ->disableOriginalConstructor()->getMock();
        $this->productScopeRewriteGenerator = $this->getMockBuilder(
            ProductScopeRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->productUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::class,
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'categoriesUrlRewriteGenerator' => $this->categoriesUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'storeViewService' => $this->storeViewService,
                'storeManager' => $this->storeManager,
            ]
        );

        $reflection = new \ReflectionClass(get_class($this->productUrlRewriteGenerator));
        $reflectionProperty = $reflection->getProperty('productScopeRewriteGenerator');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->productUrlRewriteGenerator, $this->productScopeRewriteGenerator);
    }

    public function testGenerate()
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeId = 1;
        $urls = ['dummy-url.html'];

        $productMock->expects($this->once())
            ->method('getVisibility')
            ->willReturn(2);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $productCategoriesMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCategoriesMock->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->withConsecutive(['url_key'], ['url_path'])
            ->willReturnSelf();
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($productCategoriesMock);
        $this->productScopeRewriteGenerator->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->willReturn($urls);
        $this->assertEquals($urls, $this->productUrlRewriteGenerator->generate($productMock, 1));
    }
}
