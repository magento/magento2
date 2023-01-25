<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart\Item;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions;
use Magento\Framework\Pricing\Render;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends TestCase
{
    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var MockObject
     */
    private $layout;

    /**
     * @var ImageBuilder|MockObject
     */
    private $imageBuilder;

    /**
     * @var ItemResolverInterface|MockObject
     */
    private $itemResolver;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->layout = $this->getMockForAbstractClass(LayoutInterface::class);

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->layout);

        $this->imageBuilder = $this->getMockBuilder(ImageBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemResolver = $this->createMock(
            ItemResolverInterface::class
        );

        $this->renderer = $objectManagerHelper->getObject(
            Renderer::class,
            [
                'context' => $context,
                'imageBuilder' => $this->imageBuilder,
                'itemResolver' => $this->itemResolver,
            ]
        );
    }

    public function testGetProductForThumbnail()
    {
        $product = $this->_initProduct();
        $productForThumbnail = $this->renderer->getProductForThumbnail();
        $this->assertEquals($product->getName(), $productForThumbnail->getName(), 'Invalid product was returned.');
    }

    /**
     * Initialize product.
     *
     * @return Product|MockObject
     */
    protected function _initProduct()
    {
        /** @var Product|MockObject $product */
        $product = $this->createPartialMock(
            Product::class,
            ['getName', 'getIdentities']
        );
        $product->expects($this->any())->method('getName')->willReturn('Parent Product');

        /** @var Item|MockObject $item */
        $item = $this->createMock(Item::class);
        $item->expects($this->any())->method('getProduct')->willReturn($product);

        $this->itemResolver->expects($this->any())
            ->method('getFinalProduct')
            ->with($item)
            ->willReturn($product);

        $this->renderer->setItem($item);
        return $product;
    }

    public function testGetIdentities()
    {
        $product = $this->_initProduct();
        $identities = [1 => 1, 2 => 2, 3 => 3];
        $product->expects($this->exactly(2))
            ->method('getIdentities')
            ->willReturn($identities);

        $this->assertEquals($product->getIdentities(), $this->renderer->getIdentities());
    }

    public function testGetIdentitiesFromEmptyItem()
    {
        $this->assertEmpty($this->renderer->getIdentities());
    }

    /**
     * @covers \Magento\Checkout\Block\Cart\Item\Renderer::getProductPriceHtml
     * @covers \Magento\Checkout\Block\Cart\Item\Renderer::getPriceRender
     */
    public function testGetProductPriceHtml()
    {
        $priceHtml = 'some price html';
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $priceRender = $this->getMockBuilder(Render::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->atLeastOnce())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($priceRender);

        $priceRender->expects($this->once())
            ->method('render')
            ->with(
                ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST
                ]
            )->willReturn($priceHtml);

        $this->assertEquals($priceHtml, $this->renderer->getProductPriceHtml($product));
    }

    public function testGetActions()
    {
        $blockNameInLayout = 'block.name';
        $blockHtml = 'block html';

        /**
         * @var Actions|MockObject $blockMock
         */
        $blockMock = $this->getMockBuilder(Actions::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->once())
            ->method('getChildName')
            ->with($this->renderer->getNameInLayout(), 'actions')
            ->willReturn($blockNameInLayout);
        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockNameInLayout)
            ->willReturn($blockMock);

        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $blockMock->expects($this->once())
            ->method('setItem')
            ->with($itemMock);
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($blockHtml);

        $this->assertEquals($blockHtml, $this->renderer->getActions($itemMock));
    }

    public function testGetActionsWithNoBlock()
    {
        $this->layout->expects($this->once())
            ->method('getChildName')
            ->with($this->renderer->getNameInLayout(), 'actions')
            ->willReturn(false);

        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals('', $this->renderer->getActions($itemMock));
    }

    public function testGetImage()
    {
        $imageId = 'test_image_id';
        $attributes = [];
        $product = $this->createMock(Product::class);
        $imageMock = $this->createMock(Image::class);

        $this->imageBuilder->expects($this->once())
            ->method('create')
            ->with($product, $imageId, $attributes)
            ->willReturn($imageMock);

        $this->assertInstanceOf(
            Image::class,
            $this->renderer->getImage($product, $imageId, $attributes)
        );
    }
}
