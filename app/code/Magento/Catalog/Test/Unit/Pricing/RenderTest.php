<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Render;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Test\Unit\ManagerStub;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RenderTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Render
     */
    protected $object;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layout;

    /**
     * @var MockObject
     */
    protected $pricingRenderBlock;

    protected function setUp(): void
    {
        $this->registry = $this->createPartialMock(Registry::class, ['registry']);

        $this->pricingRenderBlock = $this->createMock(PricingRender::class);

        $this->layout = $this->createMock(Layout::class);

        $eventManager = $this->createMock(ManagerStub::class);
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $context = $this->createMock(Context::class);
        $context->method('getEventManager')->willReturn($eventManager);
        $context->method('getLayout')->willReturn($this->layout);
        $context->method('getScopeConfig')->willReturn($scopeConfigMock);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            Render::class,
            [
                'context' => $context,
                'registry' => $this->registry,
                'data' => [
                    'price_render' => 'test_price_render',
                    'price_type_code' => 'test_price_type_code',
                    'module_name' => 'test_module_name',
                ]
            ]
        );
    }

    public function testToHtmlProductFromRegistry()
    {
        $expectedValue = 'string';

        $product = $this->createMock(Product::class);

        $this->layout->method('getBlock')->willReturn($this->pricingRenderBlock);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($product);

        $arguments = $this->object->getData();
        $arguments['render_block'] = $this->object;
        $this->pricingRenderBlock->expects($this->any())
            ->method('render')
            ->with(
                'test_price_type_code',
                $product,
                $arguments
            )
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->object->toHtml());
    }

    public function testToHtmlProductFromParentBlock()
    {
        $expectedValue = 'string';

        $product = $this->createMock(Product::class);

        $this->registry->expects($this->never())
            ->method('registry');

        $parentBlock = $this->createPartialMockWithReflection(
            Render::class,
            ['getProductItem']
        );
        $parentBlock->method('getProductItem')->willReturn($product);

        $arguments = $this->object->getData();
        $arguments['render_block'] = $this->object;

        $this->layout->expects($this->once())
            ->method('getParentName')
            ->willReturn('parent_name');

        $this->layout->method('getBlock')->willReturnCallback(function ($name) use ($parentBlock) {
            if ($name === 'test_price_render') {
                return $this->pricingRenderBlock;
            }
            if ($name === 'parent_name') {
                return $parentBlock;
            }
            return null;
        });

        $this->pricingRenderBlock->expects($this->once())
            ->method('render')
            ->with('test_price_type_code', $product, $this->callback(function ($args) {
                return isset($args['render_block']);
            }))
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->object->toHtml());
    }
}
