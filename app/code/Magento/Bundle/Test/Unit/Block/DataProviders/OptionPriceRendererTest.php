<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\DataProviders;

use Magento\Bundle\Block\DataProviders\OptionPriceRenderer;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Render;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test additional data for bundle options
 */
class OptionPriceRendererTest extends TestCase
{
    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var OptionPriceRenderer
     */
    private $renderer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->layoutMock = $this->createMock(
            LayoutInterface::class
        );

        $this->renderer = $objectManager->getObject(
            OptionPriceRenderer::class,
            ['layout' => $this->layoutMock]
        );
    }

    /**
     * Test to render Tier price html
     *
     * @return void
     */
    public function testRenderTierPrice(): void
    {
        $expectedHtml = 'tier price html';
        $expectedArguments = ['zone' => Render::ZONE_ITEM_OPTION];

        $productMock = $this->createMock(Product::class);

        $priceRenderer = $this->getMockBuilder(BlockInterface::class)
            ->addMethods(['render'])
            ->onlyMethods(['toHtml'])
            ->getMockForAbstractClass();
        $priceRenderer->expects($this->once())
            ->method('render')
            ->with('tier_price', $productMock, $expectedArguments)
            ->willReturn($expectedHtml);

        $this->layoutMock->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($priceRenderer);

        $this->assertEquals(
            $expectedHtml,
            $this->renderer->renderTierPrice($productMock),
            'Render Tier price is wrong'
        );
    }

    /**
     * Test to render Tier price html when render block is not exists
     *
     * @return void
     */
    public function testRenderTierPriceNotExist(): void
    {
        $productMock = $this->createMock(Product::class);

        $this->layoutMock->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn(false);

        $this->assertEquals(
            '',
            $this->renderer->renderTierPrice($productMock),
            'Render Tier price is wrong'
        );
    }
}
