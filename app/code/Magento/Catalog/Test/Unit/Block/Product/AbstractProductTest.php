<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Block\Product\View\Type\Simple;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractProductTest extends TestCase
{
    /**
     * @var Simple
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $productContextMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var ImageBuilder|MockObject
     */
    protected $imageBuilder;

    /**
     * Set up mocks and tested class
     * Child class is used as the tested class is declared abstract
     */
    protected function setUp(): void
    {
        $this->productContextMock = $this->createPartialMock(
            Context::class,
            ['getLayout', 'getStockRegistry', 'getImageBuilder']
        );
        $arrayUtilsMock = $this->createMock(ArrayUtils::class);
        $this->layoutMock = $this->createPartialMock(Layout::class, ['getBlock']);
        $this->stockRegistryMock = $this->getMockForAbstractClass(
            StockRegistryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStockItem']
        );

        $this->imageBuilder = $this->createPartialMock(ImageBuilder::class, ['create']);

        $this->productContextMock->expects($this->once())
            ->method('getStockRegistry')
            ->willReturn($this->stockRegistryMock);
        $this->productContextMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->productContextMock->expects($this->once())
            ->method('getImageBuilder')
            ->willReturn($this->imageBuilder);

        $this->block = new Simple(
            $this->productContextMock,
            $arrayUtilsMock
        );
    }

    /**
     * Test for method getProductPrice
     *
     * @covers \Magento\Catalog\Block\Product\AbstractProduct::getProductPriceHtml
     * @covers \Magento\Catalog\Block\Product\AbstractProduct::getProductPrice
     */
    public function testGetProductPrice()
    {
        $expectedPriceHtml = '<html>Expected Price html with price $30</html>';
        $priceRenderBlock = $this->createPartialMock(Render::class, ['render']);
        $product = $this->createMock(Product::class);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($priceRenderBlock);
        $priceRenderBlock->expects($this->once())
            ->method('render')
            ->willReturn($expectedPriceHtml);

        $this->assertEquals($expectedPriceHtml, $this->block->getProductPrice($product));
    }

    /**
     * Test testGetProductPriceHtml
     */
    public function testGetProductPriceHtml()
    {
        $expectedPriceHtml = '<html>Expected Price html with price $30</html>';
        $priceRenderBlock = $this->createPartialMock(Render::class, ['render']);
        $product = $this->createMock(Product::class);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($priceRenderBlock);

        $priceRenderBlock->expects($this->once())
            ->method('render')
            ->willReturn($expectedPriceHtml);

        $this->assertEquals(
            $expectedPriceHtml,
            $this->block->getProductPriceHtml($product, 'price_code', 'zone_code')
        );
    }

    /**
     * Run test getMinimalQty method
     *
     * @param int $minSale
     * @param int|null $result
     * @return void
     *
     * @dataProvider dataProviderGetMinimalQty
     */
    public function testGetMinimalQty($minSale, $result)
    {
        $id = 10;
        $websiteId = 99;

        $productMock = $this->createPartialMock(Product::class, ['getId', 'getStore']);
        $storeMock = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $stockItemMock = $this->getMockForAbstractClass(
            StockItemInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getMinSaleQty']
        );

        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($id, $websiteId)
            ->willReturn($stockItemMock);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $stockItemMock->expects($this->once())
            ->method('getMinSaleQty')
            ->willReturn($minSale);

        /** @var Product|MockObject $productMock */
        $this->assertEquals($result, $this->block->getMinimalQty($productMock));
    }

    /**
     * Data for getMinimalQty method
     *
     * @return array
     */
    public function dataProviderGetMinimalQty()
    {
        return [
            [
                'minSale' => 10,
                'result' => 10,
            ],
            [
                'minSale' => 0,
                'result' => null
            ]
        ];
    }

    public function testGetImage()
    {
        $imageId = 'test_image_id';
        $attributes = [];
        $productMock = $this->createMock(Product::class);
        $imageMock = $this->createMock(Image::class);
        $this->imageBuilder->expects(static::once())
            ->method('create')
            ->willReturn($imageMock);

        $image = $this->block->getImage($productMock, $imageId, $attributes);

        static::assertInstanceOf(Image::class, $image);
    }
}
