<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Render;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Test\Unit\ManagerStub;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RenderTest extends TestCase
{
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

        $this->pricingRenderBlock = $this->createMock(\Magento\Framework\Pricing\Render::class);

        $this->layout = $this->createMock(Layout::class);

        $eventManager = $this->createMock(ManagerStub::class);
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $context = $this->createMock(Context::class);
        $context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);

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

        $this->layout->expects($this->any())
            ->method('getBlock')
            ->willReturn($this->pricingRenderBlock);

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

        $block = $this->getMockBuilder(\Magento\Framework\Pricing\Render::class)->addMethods(['getProductItem'])
            ->onlyMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();

        $arguments = $this->object->getData();
        $arguments['render_block'] = $this->object;
        $block->expects($this->any())
            ->method('render')
            ->with(
                'test_price_type_code',
                $product,
                $arguments
            )
            ->willReturn($expectedValue);

        $block->expects($this->any())
            ->method('getProductItem')
            ->willReturn($product);

        $this->layout->expects($this->once())
            ->method('getParentName')
            ->willReturn('parent_name');

        $this->layout->expects($this->any())
            ->method('getBlock')
            ->willReturn($block);

        $this->assertEquals($expectedValue, $this->object->toHtml());
    }
}
