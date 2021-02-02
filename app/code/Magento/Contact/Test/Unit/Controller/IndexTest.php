<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Controller;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class IndexTest extends \PHPUnit\Framework\TestCase
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
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    protected function setUp(): void
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
            ->willReturn(
                
                    $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
                
            );

        $context->expects($this->any())
            ->method('getResponse')
            ->willReturn(
                
                    $this->getMockBuilder(ResponseInterface::class)->getMockForAbstractClass()
                
            );

        $this->controller = new \Magento\Contact\Test\Unit\Controller\Stub\IndexStub(
            $context,
            $this->configMock
        );
    }

    /**
     * Dispatch test
     *
     */
    public function testDispatch()
    {
        $this->expectException(\Magento\Framework\Exception\NotFoundException::class);

        $this->configMock->method('isEnabled')->willReturn(false);

        $this->controller->dispatch(
            $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
        );
    }
}
