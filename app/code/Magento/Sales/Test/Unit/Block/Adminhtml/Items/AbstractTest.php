<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Items;

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
        $renderer = $this->getMock('Magento\Framework\View\Element\AbstractBlock', [], [], '', false);
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getChildName', 'getBlock', 'getGroupChildNames', '__wakeup'],
            [],
            '',
            false
        );
        $layout->expects(
            $this->at(0)
        )->method(
            'getChildName'
        )->with(
            null,
            'some-type'
        )->will(
            $this->returnValue('some-block-name')
        );
        $layout->expects(
            $this->at(1)
        )->method(
            'getBlock'
        )->with(
            'some-block-name'
        )->will(
            $this->returnValue($renderer)
        );

        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            'Magento\Sales\Block\Adminhtml\Items\AbstractItems',
            [
                'context' => $this->_objectManager->getObject(
                    'Magento\Backend\Block\Template\Context',
                    ['layout' => $layout]
                )
            ]
        );

        $this->assertSame($renderer, $block->getItemRenderer('some-type'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Renderer for type "some-type" does not exist.
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $renderer = $this->getMock('StdClass');
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getChildName', 'getBlock', '__wakeup'],
            [],
            '',
            false
        );
        $layout->expects(
            $this->at(0)
        )->method(
            'getChildName'
        )->with(
            null,
            'some-type'
        )->will(
            $this->returnValue('some-block-name')
        );
        $layout->expects(
            $this->at(1)
        )->method(
            'getBlock'
        )->with(
            'some-block-name'
        )->will(
            $this->returnValue($renderer)
        );

        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            'Magento\Sales\Block\Adminhtml\Items\AbstractItems',
            [
                'context' => $this->_objectManager->getObject(
                    'Magento\Backend\Block\Template\Context',
                    ['layout' => $layout]
                )
            ]
        );

        $block->getItemRenderer('some-type');
    }
}
