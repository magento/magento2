<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing;

/**
 * Class RenderTest
 */
class RenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Render
     */
    protected $object;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layout;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $pricingRenderBlock;

    protected function setUp(): void
    {
        $this->registry = $this->createPartialMock(\Magento\Framework\Registry::class, ['registry']);

        $this->pricingRenderBlock = $this->createMock(\Magento\Framework\Pricing\Render::class);

        $this->layout = $this->createMock(\Magento\Framework\View\Layout::class);

        $eventManager = $this->createMock(\Magento\Framework\Event\Test\Unit\ManagerStub::class);
        $scopeConfigMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\Catalog\Pricing\Render::class,
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

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);

        $this->layout->expects($this->any())
            ->method('getBlock')
            ->willReturn($this->pricingRenderBlock);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with($this->equalTo('product'))
            ->willReturn($product);

        $arguments = $this->object->getData();
        $arguments['render_block'] = $this->object;
        $this->pricingRenderBlock->expects($this->any())
            ->method('render')
            ->with(
                $this->equalTo('test_price_type_code'),
                $this->equalTo($product),
                $this->equalTo($arguments)
            )
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->object->toHtml());
    }

    public function testToHtmlProductFromParentBlock()
    {
        $expectedValue = 'string';

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);

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
