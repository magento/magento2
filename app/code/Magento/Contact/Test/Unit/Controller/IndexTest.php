<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Controller;

use Magento\Contact\Model\ConfigInterface;
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
     * Module config instance
     *
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();

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
            $this->configMock
        );
    }

    /**
     * Dispatch test
     *
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testDispatch()
    {
        $this->configMock->method('isEnabled')->willReturn(false);

        $this->controller->dispatch(
            $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
        );
    }
}
