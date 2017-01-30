<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Controller;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Controller instance
     *
     * @var \Magento\Contact\Controller\Index
     */
    protected $_controller;

    /**
     * Scope config instance
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

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
            ['getRequest', 'getResponse'],
            [],
            '',
            false
        );

        $context->expects($this->any())
            ->method('getRequest')
            ->will(
                $this->returnValue(
                    $this->getMockForAbstractClass('\Magento\Framework\App\RequestInterface', [], '', false)
                )
            );

        $context->expects($this->any())
            ->method('getResponse')
            ->will(
                $this->returnValue(
                    $this->getMockForAbstractClass('\Magento\Framework\App\ResponseInterface', [], '', false)
                )
            );

        $this->_controller = new \Magento\Contact\Test\Unit\Controller\Stub\IndexStub(
            $context,
            $this->getMock('\Magento\Framework\Mail\Template\TransportBuilder', [], [], '', false),
            $this->getMockForAbstractClass('\Magento\Framework\Translate\Inline\StateInterface', [], '', false),
            $this->_scopeConfig,
            $this->getMockForAbstractClass('\Magento\Store\Model\StoreManagerInterface', [], '', false)
        );
    }

    /**
     * Dispatch test
     *
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testDispatch()
    {
        $this->_scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                \Magento\Contact\Controller\Index::XML_PATH_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->will($this->returnValue(false));

        $this->_controller->dispatch(
            $this->getMockForAbstractClass('\Magento\Framework\App\RequestInterface', [], '', false)
        );
    }
}
