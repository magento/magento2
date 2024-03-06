<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogUrlRewrite\Model\GetVisibleForStores;
use Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductUrlRewriteGeneratorTest extends TestCase
{
    /** @var MockObject */
    protected $canonicalUrlRewriteGenerator;

    /** @var MockObject */
    protected $currentUrlRewritesRegenerator;

    /** @var MockObject */
    protected $categoriesUrlRewriteGenerator;

    /** @var MockObject */
    protected $anchorUrlRewriteGenerator;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var StoreViewService|MockObject */
    protected $storeViewService;

    /** @var Product|MockObject */
    protected $product;

    /** @var ObjectRegistryFactory|MockObject */
    protected $objectRegistryFactory;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var Collection|MockObject */
    protected $categoriesCollection;

    /** @var MockObject  */
    private $productScopeRewriteGenerator;

    /**
     * @var GetVisibleForStores|MockObject
     */
    private $visibleForStores;

    /**
     * Test method
     */
    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);
        $this->categoriesCollection = $this->getMockBuilder(
            Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->any())->method('getCategoryCollection')
            ->willReturn($this->categoriesCollection);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->currentUrlRewritesRegenerator = $this->getMockBuilder(
            CurrentUrlRewritesRegenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
            CanonicalUrlRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->categoriesUrlRewriteGenerator = $this->getMockBuilder(
            CategoriesUrlRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->anchorUrlRewriteGenerator = $this->getMockBuilder(
            AnchorUrlRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->objectRegistryFactory = $this->getMockBuilder(
            ObjectRegistryFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])->getMock();
        $this->storeViewService = $this->getMockBuilder(StoreViewService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productScopeRewriteGenerator = $this->getMockBuilder(
            ProductScopeRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->visibleForStores = $this->createMock(GetVisibleForStores::class);
        $this->productUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            ProductUrlRewriteGenerator::class,
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'categoriesUrlRewriteGenerator' => $this->categoriesUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'storeViewService' => $this->storeViewService,
                'storeManager' => $this->storeManager,
                'visibleForStores' => $this->visibleForStores
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
        $productCategoriesMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCategoriesMock->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['url_key'] => $productCategoriesMock,
                ['url_path'] => $productCategoriesMock
            });
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($productCategoriesMock);
        $this->productScopeRewriteGenerator->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->willReturn($urls);
        $this->assertEquals($urls, $this->productUrlRewriteGenerator->generate($productMock, 1));
    }

    public function testGenerateForDefaultNonVisible()
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeId = 1;
        $urls = ['dummy-url.html'];

        $productMock->expects($this->once())
            ->method('getVisibility')
            ->willReturn(Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $productMock->expects($this->exactly(3))
            ->method('getStoreId')
            ->willReturn($storeId);
        $productCategoriesMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCategoriesMock->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->withConsecutive(['url_key'], ['url_path'])
            ->willReturnSelf();
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($productCategoriesMock);
        $this->visibleForStores->expects($this->once())
            ->method('execute')
            ->with($productMock)
            ->willReturn([$storeId]);
        $this->productScopeRewriteGenerator->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->willReturn($urls);
        $this->assertEquals($urls, $this->productUrlRewriteGenerator->generate($productMock, 1));
    }
}
