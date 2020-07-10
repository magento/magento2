<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator.
 */
class ProductUrlRewriteGeneratorTest extends TestCase
{
    private const STUB_URLS = ['dummy-url.html'];

    /**
     * @var ProductUrlRewriteGenerator
     */
    private $model;

    /**
     * @var ProductScopeRewriteGenerator|MockObject
     */
    private $productScopeRewriteGenerator;

    /**
     * Test method
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $categoriesCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->createMock(Product::class);
        $product->expects($this->any())
            ->method('getCategoryCollection')
            ->willReturn($categoriesCollection);
        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productScopeRewriteGenerator = $this->createMock(ProductScopeRewriteGenerator::class);

        $this->model = $objectManager->getObject(
            ProductUrlRewriteGenerator::class,
            ['storeManager' => $storeManager]
        );

        $reflection = new \ReflectionClass(get_class($this->model));
        $reflectionProperty = $reflection->getProperty('productScopeRewriteGenerator');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $this->productScopeRewriteGenerator);
    }

    /**
     * Test generate product url rewrites
     *
     * @return void
     */
    public function testGenerate(): void
    {
        $productMock = $this->createMock(Product::class);

        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $productCategoriesMock = $this->createMock(Collection::class);
        $productCategoriesMock->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->withConsecutive(['url_key'], ['url_path'])
            ->willReturnSelf();
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($productCategoriesMock);
        $this->productScopeRewriteGenerator->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->willReturn(self::STUB_URLS);

        $result = $this->model->generate($productMock, 1);

        $this->assertEquals(self::STUB_URLS, $result);
    }
}
