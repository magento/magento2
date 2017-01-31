<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Block\Product;

/**
 * Class for testing methods of AbstractProduct
 */
class AbstractProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\View\Type\Simple
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Block\Product\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productContextMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageBuilder;

    /**
     * Set up mocks and tested class
     * Child class is used as the tested class is declared abstract
     */
    protected function setUp()
    {
        $this->productContextMock = $this->getMock(
            'Magento\Catalog\Block\Product\Context',
            ['getLayout', 'getStockRegistry', 'getImageBuilder'],
            [],
            '',
            false
        );
        $arrayUtilsMock = $this->getMock('Magento\Framework\Stdlib\ArrayUtils', [], [], '', false);
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', ['getBlock'], [], '', false);
        $this->stockRegistryMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockRegistryInterface',
            [],
            '',
            false,
            true,
            true,
            ['getStockItem']
        );

        $this->imageBuilder = $this->getMockBuilder('Magento\Catalog\Block\Product\ImageBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productContextMock->expects($this->once())
            ->method('getStockRegistry')
            ->will($this->returnValue($this->stockRegistryMock));
        $this->productContextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));
        $this->productContextMock->expects($this->once())
            ->method('getImageBuilder')
            ->will($this->returnValue($this->imageBuilder));

        $this->block = new \Magento\Catalog\Block\Product\View\Type\Simple(
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
        $priceRenderBlock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false);
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->will($this->returnValue($priceRenderBlock));
        $priceRenderBlock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($expectedPriceHtml));

        $this->assertEquals($expectedPriceHtml, $this->block->getProductPrice($product));
    }

    /**
     * Test testGetProductPriceHtml
     */
    public function testGetProductPriceHtml()
    {
        $expectedPriceHtml = '<html>Expected Price html with price $30</html>';
        $priceRenderBlock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false);
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->will($this->returnValue($priceRenderBlock));

        $priceRenderBlock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($expectedPriceHtml));

        $this->assertEquals($expectedPriceHtml, $this->block->getProductPriceHtml(
            $product, 'price_code', 'zone_code'
        ));
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

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'getStore'],
            [],
            '',
            false
        );
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['getWebsiteId'],
            [],
            '',
            false
        );
        $stockItemMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockItemInterface',
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
            ->will($this->returnValue($stockItemMock));
        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));
        $productMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $stockItemMock->expects($this->once())
            ->method('getMinSaleQty')
            ->will($this->returnValue($minSale));

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productMock */
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

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $imageMock = $this->getMockBuilder('Magento\Catalog\Block\Product\Image')
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageBuilder->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('setImageId')
            ->with($imageId)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('setAttributes')
            ->with($attributes)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('create')
            ->willReturn($imageMock);

        $this->assertInstanceOf(
            'Magento\Catalog\Block\Product\Image',
            $this->block->getImage($productMock, $imageId, $attributes)
        );
    }
}
