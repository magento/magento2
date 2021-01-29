<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Items;

use Magento\Sales\Block\Items\AbstractItems;

class AbstractTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetItemRenderer()
    {
        $rendererType = 'some-type';
        $renderer = $this->createPartialMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setRenderedBlock']
        );

        $rendererList = $this->createMock(\Magento\Framework\View\Element\RendererList::class);
        $rendererList->expects(
            $this->once()
        )->method(
            'getRenderer'
        )->with(
            $rendererType,
            AbstractItems::DEFAULT_TYPE
        )->willReturn(
            $renderer
        );

        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getChildName', 'getBlock']);

        $layout->expects($this->once())->method('getChildName')->willReturn('renderer.list');

        $layout->expects(
            $this->once()
        )->method(
            'getBlock'
        )->with(
            'renderer.list'
        )->willReturn(
            $rendererList
        );

        /** @var $block \Magento\Sales\Block\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            \Magento\Sales\Block\Items\AbstractItems::class,
            [
                'context' => $this->_objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $renderer->expects($this->once())->method('setRenderedBlock')->with($block);

        $this->assertSame($renderer, $block->getItemRenderer($rendererType));
    }

    /**
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Renderer list for block "" is not defined');

        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getChildName', 'getBlock']);
        $layout->expects($this->once())->method('getChildName')->willReturn(null);

        /** @var $block \Magento\Sales\Block\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            \Magento\Sales\Block\Items\AbstractItems::class,
            [
                'context' => $this->_objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $block->getItemRenderer('some-type');
    }
}
