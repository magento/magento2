<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Quote\Model\Quote\Item;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Renderer
     */
    protected $_renderer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageBuilder;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->layout = $this->getMock(\Magento\Framework\View\LayoutInterface::class);

        $context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));

        $this->imageBuilder = $this->getMockBuilder(\Magento\Catalog\Block\Product\ImageBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_renderer = $objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Item\Renderer::class,
            [
                'context' => $context,
                'imageBuilder' => $this->imageBuilder,
            ]
        );
    }

    public function testGetProductForThumbnail()
    {
        $product = $this->_initProduct();
        $productForThumbnail = $this->_renderer->getProductForThumbnail();
        $this->assertEquals($product->getName(), $productForThumbnail->getName(), 'Invalid product was returned.');
    }

    /**
     * Initialize product.
     *
     * @return \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _initProduct()
    {
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getName', '__wakeup', 'getIdentities'],
            [],
            '',
            false
        );
        $product->expects($this->any())->method('getName')->will($this->returnValue('Parent Product'));

        /** @var Item|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
        $item->expects($this->any())->method('getProduct')->will($this->returnValue($product));

        $this->_renderer->setItem($item);
        return $product;
    }

    public function testGetIdentities()
    {
        $product = $this->_initProduct();
        $identities = [1 => 1, 2 => 2, 3 => 3];
        $product->expects($this->exactly(2))
            ->method('getIdentities')
            ->will($this->returnValue($identities));

        $this->assertEquals($product->getIdentities(), $this->_renderer->getIdentities());
    }

    public function testGetIdentitiesFromEmptyItem()
    {
        $this->assertEmpty($this->_renderer->getIdentities());
    }

    /**
     * @covers \Magento\Checkout\Block\Cart\Item\Renderer::getProductPriceHtml
     * @covers \Magento\Checkout\Block\Cart\Item\Renderer::getPriceRender
     */
    public function testGetProductPriceHtml()
    {
        $priceHtml = 'some price html';
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $priceRender = $this->getMockBuilder(\Magento\Framework\Pricing\Render::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->atLeastOnce())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->will($this->returnValue($priceRender));

        $priceRender->expects($this->once())
            ->method('render')
            ->with(
                \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            )->will($this->returnValue($priceHtml));

        $this->assertEquals($priceHtml, $this->_renderer->getProductPriceHtml($product));
    }

    public function testGetActions()
    {
        $blockNameInLayout = 'block.name';
        $blockHtml = 'block html';

        /**
         * @var \Magento\Checkout\Block\Cart\Item\Renderer\Actions|\PHPUnit_Framework_MockObject_MockObject $blockMock
         */
        $blockMock = $this->getMockBuilder(\Magento\Checkout\Block\Cart\Item\Renderer\Actions::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->once())
            ->method('getChildName')
            ->with($this->_renderer->getNameInLayout(), 'actions')
            ->willReturn($blockNameInLayout);
        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockNameInLayout)
            ->willReturn($blockMock);

        /**
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $blockMock->expects($this->once())
            ->method('setItem')
            ->with($itemMock);
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($blockHtml);

        $this->assertEquals($blockHtml, $this->_renderer->getActions($itemMock));
    }

    public function testGetActionsWithNoBlock()
    {
        $this->layout->expects($this->once())
            ->method('getChildName')
            ->with($this->_renderer->getNameInLayout(), 'actions')
            ->willReturn(false);

        /**
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals('', $this->_renderer->getActions($itemMock));
    }

    public function testGetImage()
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $imageMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\Image::class)
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
            \Magento\Catalog\Block\Product\Image::class,
            $this->_renderer->getImage($productMock, $imageId, $attributes)
        );
    }
}
