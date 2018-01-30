<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Controller
     *
     * @var \Magento\Contact\Controller\Index\Index
     */
    protected $_controller;

    /**
     * Scope config mock
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * View mock
     *
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_view;

    /**
     * Url mock
     *
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_url;

    protected function setUp()
    {
        $this->_scopeConfig = $this->getMockForAbstractClass(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            ['isSetFlag'],
            '',
            false
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
        $this->_view->expects($this->once())
            ->method('loadLayout');

        $this->_view->expects($this->once())
            ->method('renderLayout');

        $this->_controller->execute();
    }
}
