<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Contact\Controller\Index\Index|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_controller;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_view;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_url;

    public function setUp()
    {
        $this->_scopeConfig = $this->getMockForAbstractClass(
            '\Magento\Framework\App\Config\ScopeConfigInterface', ['isSetFlag'], '', false
        );
        $context = $this->getMock(
            '\Magento\Framework\App\Action\Context',
            ['getRequest', 'getResponse', 'getView', 'getUrl'],
            [],
            '',
            false
        );

        $this->_url = $this->getMockForAbstractClass('\Magento\Framework\UrlInterface', [], '', false);

        $context->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($this->_url));

        $context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue(
                $this->getMockForAbstractClass('\Magento\Framework\App\RequestInterface', [], '', false)
            ));

        $context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue(
                $this->getMockForAbstractClass('\Magento\Framework\App\ResponseInterface', [], '', false)
            ));

        $this->_view = $this->getMock(
            '\Magento\Framework\App\ViewInterface',
            [],
            [],
            '',
            false
        );

        $context->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->_view));

        $this->_controller = new \Magento\Contact\Controller\Index\Index(
            $context,
            $this->getMock('\Magento\Framework\Mail\Template\TransportBuilder', [], [], '', false),
            $this->getMockForAbstractClass('\Magento\Framework\Translate\Inline\StateInterface', [], '', false),
            $this->_scopeConfig,
            $this->getMockForAbstractClass('\Magento\Store\Model\StoreManagerInterface', [], '', false)
        );
    }

    public function testExecute()
    {
        $layout = $this->getMock(
            '\Magento\Framework\View\Layout',
            ['getBlock', 'initMessages'],
            [],
            '',
            false
        );
        $block = $this->getMockForAbstractClass(
            '\Magento\Framework\View\Element\AbstractBlock',
            ['setFormAction'],
            '',
            false
        );
        $layout->expects($this->once())
            ->method('getBlock')
            ->with('contactForm')
            ->will($this->returnValue($block));

        $this->_view->expects($this->once())
            ->method('loadLayout');

        $this->_view->expects($this->exactly(2))
            ->method('getLayout')
            ->will($this->returnValue($layout));

        $layout->expects($this->once())
            ->method('initMessages');

        $this->_view->expects($this->once())
            ->method('renderLayout');

        $this->_controller->execute();
    }
}
