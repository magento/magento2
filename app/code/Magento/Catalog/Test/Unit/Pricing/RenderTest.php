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
            ->will($this->returnValue($eventManager));
        $context->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfigMock));

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
            ->will($this->returnValue($this->pricingRenderBlock));

        $this->registry->expects($this->once())
            ->method('registry')
            ->with($this->equalTo('product'))
            ->will($this->returnValue($product));

        $arguments = $this->object->getData();
        $arguments['render_block'] = $this->object;
        $this->pricingRenderBlock->expects($this->any())
            ->method('render')
            ->with(
                $this->equalTo('test_price_type_code'),
                $this->equalTo($product),
                $this->equalTo($arguments)
            )
            ->will($this->returnValue($expectedValue));

        $this->assertEquals($expectedValue, $this->object->toHtml());
    }

    public function testToHtmlProductFromParentBlock()
    {
        $expectedValue = 'string';

        $product = $this->createMock(Product::class);

        $this->registry->expects($this->never())
            ->method('registry');

        $block = $this->createPartialMock(\Magento\Framework\Pricing\Render::class, ['getProductItem', 'render']);

        $arguments = $this->object->getData();
        $arguments['render_block'] = $this->object;
        $block->expects($this->any())
            ->method('render')
            ->with(
                $this->equalTo('test_price_type_code'),
                $this->equalTo($product),
                $this->equalTo($arguments)
            )
            ->will($this->returnValue($expectedValue));

        $block->expects($this->any())
            ->method('getProductItem')
            ->will($this->returnValue($product));

        $this->layout->expects($this->once())
            ->method('getParentName')
            ->will($this->returnValue('parent_name'));

        $this->layout->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValue($block));

        $this->assertEquals($expectedValue, $this->object->toHtml());
    }
}
