<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Items;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\Layout;
use Magento\Sales\Block\Items\AbstractItems;
use PHPUnit\Framework\TestCase;

class AbstractTest extends TestCase
{
    /** @var ObjectManager  */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
    }

    public function testGetItemRenderer()
    {
        $rendererType = 'some-type';
        $renderer = $this->createPartialMock(
            AbstractBlock::class,
            ['setRenderedBlock']
        );

        $rendererList = $this->createMock(RendererList::class);
        $rendererList->expects(
            $this->once()
        )->method(
            'getRenderer'
        )->with(
            $rendererType,
            AbstractItems::DEFAULT_TYPE
        )->will(
            $this->returnValue($renderer)
        );

        $layout = $this->createPartialMock(Layout::class, ['getChildName', 'getBlock']);

        $layout->expects($this->once())->method('getChildName')->will($this->returnValue('renderer.list'));

        $layout->expects(
            $this->once()
        )->method(
            'getBlock'
        )->with(
            'renderer.list'
        )->will(
            $this->returnValue($rendererList)
        );

        /** @var \Magento\Sales\Block\Items\AbstractItems $block */
        $block = $this->_objectManager->getObject(
            AbstractItems::class,
            [
                'context' => $this->_objectManager->getObject(
                    Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $renderer->expects($this->once())->method('setRenderedBlock')->with($block);

        $this->assertSame($renderer, $block->getItemRenderer($rendererType));
    }

    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Renderer list for block "" is not defined');
        $layout = $this->createPartialMock(Layout::class, ['getChildName', 'getBlock']);
        $layout->expects($this->once())->method('getChildName')->will($this->returnValue(null));

        /** @var \Magento\Sales\Block\Items\AbstractItems $block */
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
}
