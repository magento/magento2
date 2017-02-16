<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Controller;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Controller instance
     *
     * @var \Magento\Contact\Controller\Index
     */
    private $controller;

    /**
     * Scope config instance
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    protected function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->setMethods(
            ['isSetFlag']
        )->getMockForAbstractClass();

        $context = $this->getMockBuilder(
            \Magento\Framework\App\Action\Context::class
        )->setMethods(
            ['getRequest', 'getResponse']
        )->disableOriginalConstructor(
        )->getMock();

        $context->expects($this->any())
            ->method('getRequest')
            ->will(
                $this->returnValue(
                    $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
                )
            );

        $context->expects($this->any())
            ->method('getResponse')
            ->will(
                $this->returnValue(
                    $this->getMockBuilder(ResponseInterface::class)->getMockForAbstractClass()
                )
            );

        $this->controller = new \Magento\Contact\Test\Unit\Controller\Stub\IndexStub(
            $context,
            $this->scopeConfig
        );
    }

    /**
     * Dispatch test
     *
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testDispatch()
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                \Magento\Contact\Controller\Index::XML_PATH_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->will($this->returnValue(false));

        $this->controller->dispatch(
            $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
        );
    }
}
