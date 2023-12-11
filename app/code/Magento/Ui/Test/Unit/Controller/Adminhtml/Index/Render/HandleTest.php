<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index\Render;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\View\Element\UiComponent\ContextFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Ui\Controller\Adminhtml\Index\Render\Handle;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HandleTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject
     */
    protected $viewMock;

    /**
     * @var Handle
     */
    protected $controller;

    /**
     * @var AuthorizationInterface|MockObject
     */
    private $authorizationMock;

    /**
     * @var MockObject
     */
    private $uiComponentContextMock;

    /**
     * @var UiComponentInterface|MockObject
     */
    private $uiComponentMock;

    /**
     * @var MockObject
     */
    private $uiFactoryMock;

    /**
     * @var DataProviderInterface|MockObject
     */
    private $dataProviderMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($this->requestMock);

        $this->responseMock = $this->createMock(Response::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getResponse')->willReturn($this->responseMock);

        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getView')->willReturn($this->viewMock);
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMockForAbstractClass();
        $this->authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn(true);
        $this->uiComponentContextMock = $this->getMockForAbstractClass(
            ContextInterface::class
        );
        $this->uiComponentMock = $this->getMockForAbstractClass(
            UiComponentInterface::class
        );
        $this->dataProviderMock = $this->getMockForAbstractClass(
            DataProviderInterface::class
        );
        $this->uiComponentContextMock->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->uiFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
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
        $contextMock = $this->createMock(ContextFactory::class);
        $this->controller = new Handle($this->contextMock, $this->uiFactoryMock, $contextMock);
    }

    public function testExecuteNoButtons()
    {
        $result = '';
        $this->requestMock->expects($this->exactly(3))->method('getParam')->willReturn($result);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->with(['default', $result], true, true, false);
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
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

        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);

        $layoutMock->expects($this->exactly(2))->method('getBlock');

        $this->responseMock->expects($this->once())->method('appendBody')->with('');
        $this->controller->execute();
    }
}
