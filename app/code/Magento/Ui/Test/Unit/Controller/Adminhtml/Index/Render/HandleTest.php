<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index\Render;

use Magento\Ui\Controller\Adminhtml\Index\Render\Handle;

class HandleTest extends \PHPUnit_Framework_TestCase
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
        $this->contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->componentFactoryMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
            [],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->contextMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($this->requestMock);

        $this->responseMock = $this->getMock('Magento\Framework\HTTP\PhpEnvironment\Response', [], [], '', false);
        $this->contextMock->expects($this->atLeastOnce())->method('getResponse')->willReturn($this->responseMock);

        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
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
        $layoutMock = $this->getMock('\Magento\Framework\View\LayoutInterface');
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

        $layoutMock = $this->getMock('\Magento\Framework\View\LayoutInterface');
        $this->viewMock->expects($this->exactly(2))->method('getLayout')->willReturn($layoutMock);

        $layoutMock->expects($this->exactly(2))->method('getBlock');

        $this->responseMock->expects($this->once())->method('appendBody')->with('');
        $this->controller->execute();
    }
}
