<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index\Render;

use Magento\Ui\Controller\Adminhtml\Index\Render\Handle;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
    protected $viewMock;

    /**
     * @var Handle
     */
    protected $controller;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentContextMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $uiFactoryMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface|
     *      \PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderMock;

    public function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);

        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($this->requestMock);

        $this->responseMock = $this->createMock(\Magento\Framework\HTTP\PhpEnvironment\Response::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getResponse')->willReturn($this->responseMock);

        $this->viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getView')->willReturn($this->viewMock);
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->getMockForAbstractClass();
        $this->authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn(true);
        $this->uiComponentContextMock = $this->getMockForAbstractClass(
            ContextInterface::class
        );
        $this->uiComponentMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class
        );
        $this->dataProviderMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface::class
        );
        $this->uiComponentContextMock->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->uiFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiComponentMock->expects($this->any())
            ->method('getContext')
            ->willReturn($this->uiComponentContextMock);
        $this->uiFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->uiComponentMock);
        $this->dataProviderMock->expects($this->once())
            ->method('getConfigData')
            ->willReturn([]);
        $contextMock = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextFactory::class);
        $this->controller = new Handle($this->contextMock, $this->uiFactoryMock, $contextMock);
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
        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);

        $layoutMock->expects($this->exactly(2))->method('getBlock');

        $this->responseMock->expects($this->once())->method('appendBody')->with('');
        $this->controller->execute();
    }
}
