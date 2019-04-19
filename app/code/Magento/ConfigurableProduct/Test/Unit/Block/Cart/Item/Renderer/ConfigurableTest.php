<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Block\Cart\Item\Renderer;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable as Renderer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configManager;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productConfigMock;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\View\Layout|PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var Renderer
     */
    private $renderer;

    protected function setUp()
    {
        parent::setUp();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->contextMock = $this->createPartialMock(\Magento\Backend\Block\Template\Context::class, ['getLayout']);
        $this->layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getBlock']);
        $this->contextMock->expects($this->once())->method('getLayout')->willReturn($this->layoutMock);

        $this->configManager = $this->createMock(\Magento\Framework\View\ConfigInterface::class);
        $this->imageHelper = $this->createPartialMock(
            \Magento\Catalog\Helper\Image::class,
            ['init', 'resize', '__toString']
        );
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->productConfigMock = $this->createMock(\Magento\Catalog\Helper\Product\Configuration::class);
        $this->renderer = $objectManagerHelper->getObject(
            \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::class,
            [
                'viewConfig' => $this->configManager,
                'imageHelper' => $this->imageHelper,
                'scopeConfig' => $this->scopeConfig,
                'productConfig' => $this->productConfigMock,
                'context' => $this->contextMock,
            ]
        );
    }

    public function testGetOptionList()
    {
        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->renderer->setItem($itemMock);
        $this->productConfigMock->expects($this->once())->method('getOptions')->with($itemMock);
        $this->renderer->getOptionList();
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->exactly(2))->method('getIdentities')->will($this->returnValue($productTags));
        $item = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $item->expects($this->exactly(2))->method('getProduct')->will($this->returnValue($product));
        $this->renderer->setItem($item);
        $this->assertEquals(array_merge($productTags, $productTags), $this->renderer->getIdentities());
    }

    /**
     * Product price renderer test.
     *
     * @return void
     */
    public function testGetProductPriceHtml()
    {
        $priceHtml = 'some price html';
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $item = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $item->expects($this->atLeastOnce())->method('getProduct')->willReturn($productMock);

        $priceRenderMock = $this->getMockBuilder(\Magento\Framework\Pricing\Render::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($priceRenderMock);

        $priceRenderMock->expects($this->once())
            ->method('render')
            ->with(
                \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
                $productMock,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ]
            )->willReturn($priceHtml);

        $this->renderer->setItem($item);
        $this->assertEquals($priceHtml, $this->renderer->getProductPriceHtml($productMock));
    }
}
