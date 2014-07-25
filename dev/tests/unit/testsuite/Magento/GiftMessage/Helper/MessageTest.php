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
namespace Magento\GiftMessage\Helper;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutFactoryMock;

    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $helper;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->layoutFactoryMock = $this->getMock('\Magento\Framework\View\LayoutFactory', array(), array(), '', false);

        $this->helper = $objectManager->getObject('\Magento\GiftMessage\Helper\Message', array(
            'layoutFactory' => $this->layoutFactoryMock,
            'skipMessageCheck' => array('onepage_checkout'),
        ));
    }

    /**
     * Make sure that isMessagesAvailable is not called
     */
    public function testGetInlineForCheckout()
    {
        $expectedHtml = '<a href="here">here</a>';
        $layoutMock = $this->getMock('\Magento\Framework\View\Layout', array(), array(), '', false);
        $entityMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $inlineMock = $this->getMock(
            'Magento\GiftMessage\Block\Message\Inline',
            array('setId', 'setDontDisplayContainer', 'setEntity', 'setCheckoutType', 'toHtml'),
            array(),
            '',
            false
        );

        $this->layoutFactoryMock->expects($this->once())->method('create')->will($this->returnValue($layoutMock));
        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($inlineMock));

        $inlineMock->expects($this->once())->method('setId')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setDontDisplayContainer')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setEntity')->with($entityMock)->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setCheckoutType')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('toHtml')->will($this->returnValue($expectedHtml));

        $this->assertEquals($expectedHtml, $this->helper->getInline('onepage_checkout', $entityMock));
    }
}
