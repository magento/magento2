<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Items;

use \Magento\Sales\Block\Items\AbstractItems;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetItemRenderer()
    {
        $rendererType = 'some-type';
        $renderer = $this->getMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setRenderedBlock'],
            [],
            '',
            false
        );

        $rendererList = $this->getMock(\Magento\Framework\View\Element\RendererList::class, [], [], '', false);
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

        $layout = $this->getMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock'],
            [],
            '',
            false
        );

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
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Renderer list for block "" is not defined
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $layout = $this->getMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock'],
            [],
            '',
            false
        );
        $layout->expects($this->once())->method('getChildName')->will($this->returnValue(null));

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
