<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Items;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\Layout;
use Magento\Sales\Block\Items\AbstractItems;
use Magento\Sales\ViewModel\ItemRendererTypeResolverInterface;
use PHPUnit\Framework\TestCase;

class AbstractTest extends TestCase
{
    /** @var ObjectManager  */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
    }

    public function testGetItemRenderer(): void
    {
        $rendererType = 'some-type';
        $renderer = $this->getRendererMock('some output');
        $rendererList = $this->getRendererListMock([$rendererType => $renderer]);
        $block = $this->getBlock($rendererList);
        $this->assertSame($renderer, $block->getItemRenderer($rendererType));
        $this->assertSame($block, $renderer->getRenderedBlock());
    }

    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Renderer list for block "" is not defined');
        $layout = $this->createPartialMock(Layout::class, ['getChildName', 'getBlock']);
        $layout->expects($this->once())->method('getChildName')->willReturn(null);

        /** @var AbstractItems $block */
        $block = $this->_objectManager->getObject(
            AbstractItems::class,
            [
                'context' => $this->_objectManager->getObject(
                    Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $block->getItemRenderer('some-type');
    }

    /**
     * @param string $type
     * @param string|null $resolvedType
     * @param string $expected
     * @dataProvider getItemHtmlDataProvider
     */
    public function testGetItemHtml(string $type, ?string $resolvedType, string $expected): void
    {
        $renderers = [
            'type1' => $this->getRendererMock('type 1 renderer'),
            'type2' => $this->getRendererMock('type 2 renderer'),
        ];
        $rendererList = $this->getRendererListMock($renderers);
        $block = $this->getBlock($rendererList);
        $item = new DataObject(['product_type' => $type]);
        $itemRendererTypeResolver = $this->getMockBuilder(ItemRendererTypeResolverInterface::class)
            ->getMockForAbstractClass();
        $itemRendererTypeResolver->method('resolve')
            ->willReturn($resolvedType);
        $block->setData($type . '_renderer_type_resolver', $itemRendererTypeResolver);
        $this->assertEquals($expected, $block->getItemHtml($item));
    }

    /**
     * @return array
     */
    public function getItemHtmlDataProvider(): array
    {
        return [
            [
                'type1',
                null,
                'type 1 renderer'
            ],
            [
                'type1',
                'type2',
                'type 2 renderer'
            ],
            [
                'type3',
                null,
                'default renderer'
            ],
            [
                'type3',
                'type1',
                'type 1 renderer'
            ],
        ];
    }

    /**
     * @param string $html
     * @return AbstractBlock
     */
    private function getRendererMock(string $html): AbstractBlock
    {
        $renderer = $this->getMockBuilder(AbstractBlock::class)
            ->onlyMethods(['toHtml'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $renderer->method('toHtml')
            ->willReturn($html);

        return $renderer;
    }

    /**
     * @param array $renderers
     * @return RendererList
     */
    private function getRendererListMock(array $renderers): RendererList
    {
        $renderers[AbstractItems::DEFAULT_TYPE] = $this->getRendererMock('default renderer');
        $rendererList = $this->createMock(RendererList::class);
        $rendererList->expects($this->once())
            ->method('getRenderer')
            ->willReturnCallback(
                function ($type, $default) use ($renderers) {
                    return $renderers[$type] ?? $renderers[$default] ?? null;
                }
            );

        return $rendererList;
    }

    /**
     * @param RendererList $rendererList
     * @return AbstractItems
     */
    private function getBlock(RendererList $rendererList): AbstractItems
    {
        $layout = $this->createPartialMock(
            Layout::class,
            [
                'getChildName',
                'getBlock'
            ]
        );

        $layout->expects($this->once())
            ->method('getChildName')
            ->willReturn('renderer.list');

        $layout->expects($this->once())
            ->method('getBlock')
            ->with('renderer.list')
            ->willReturn($rendererList);

        $context = $this->_objectManager->getObject(
            Context::class,
            ['layout' => $layout]
        );

        return new AbstractItems($context);
    }
}
