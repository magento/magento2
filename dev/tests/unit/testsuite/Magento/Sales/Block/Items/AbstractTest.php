<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Items;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGetItemRenderer()
    {
        $rendererType = 'some-type';
        $renderer = $this->getMock(
            'Magento\Framework\View\Element\AbstractBlock',
            array('setRenderedBlock'),
            array(),
            '',
            false
        );

        $rendererList = $this->getMock('Magento\Framework\View\Element\RendererList', array(), array(), '', false);
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
            'Magento\Framework\View\Layout',
            array('getChildName', 'getBlock'),
            array(),
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
            'Magento\Sales\Block\Items\AbstractItems',
            array(
                'context' => $this->_objectManager->getObject(
                    'Magento\Backend\Block\Template\Context',
                    array('layout' => $layout)
                )
            )
        );

        $renderer->expects($this->once())->method('setRenderedBlock')->with($block);

        $this->assertSame($renderer, $block->getItemRenderer($rendererType));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Renderer list for block "" is not defined
     */
    public function te1stGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            array('getChildName', 'getBlock'),
            array(),
            '',
            false
        );
        $layout->expects($this->once())->method('getChildName')->will($this->returnValue(null));

        /** @var $block \Magento\Sales\Block\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            'Magento\Sales\Block\Items\AbstractItems',
            array(
                'context' => $this->_objectManager->getObject(
                    'Magento\Backend\Block\Template\Context',
                    array('layout' => $layout)
                )
            )
        );

        $block->getItemRenderer('some-type');
    }
}
