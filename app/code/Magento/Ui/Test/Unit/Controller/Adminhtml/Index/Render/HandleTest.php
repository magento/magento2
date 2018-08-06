<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index\Render;

use Magento\Ui\Controller\Adminhtml\Index\Render\Handle;

class HandleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $componentFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var Handle
     */
    protected $controller;

    public function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->componentFactoryMock = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);

        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($this->requestMock);

        $this->responseMock = $this->createMock(\Magento\Framework\HTTP\PhpEnvironment\Response::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getResponse')->willReturn($this->responseMock);

        $this->viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getView')->willReturn($this->viewMock);

        $this->controller = new Handle($this->contextMock, $this->componentFactoryMock);
    }

    public function testExecuteNoButtons()
    {
        $result = '';
        $this->requestMock->expects($this->exactly(3))->method('getParam')->willReturn($result);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->with(['default', $result], true, true, false);
        $layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);

        $layoutMock->expects($this->once())->method('getBlock');

        $this->responseMock->expects($this->once())->method('appendBody')->with('');
        $this->controller->execute();
    }

    public function testExecute()
    {
        $result = 'SomeRequestParam';
        $this->requestMock->expects($this->exactly(3))->method('getParam')->willReturn($result);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->with(['default', $result], true, true, false);

        $layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->viewMock->expects($this->exactly(2))->method('getLayout')->willReturn($layoutMock);

        $layoutMock->expects($this->exactly(2))->method('getBlock');

        $this->responseMock->expects($this->once())->method('appendBody')->with('');
        $this->controller->execute();
    }
}
