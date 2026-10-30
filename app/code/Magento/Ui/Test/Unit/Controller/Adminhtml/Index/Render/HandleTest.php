<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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

        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($this->requestMock);

        $this->responseMock = $this->createMock(Response::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getResponse')->willReturn($this->responseMock);

        $this->viewMock = $this->createMock(ViewInterface::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getView')->willReturn($this->viewMock);
        $this->authorizationMock = $this->createMock(AuthorizationInterface::class);
        $this->authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn(true);
        $this->uiComponentContextMock = $this->createMock(
            ContextInterface::class
        );
        $this->uiComponentMock = $this->createMock(
            UiComponentInterface::class
        );
        $this->dataProviderMock = $this->createMock(
            DataProviderInterface::class
        );
        $this->uiComponentContextMock->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->uiFactoryMock = $this->createMock(UiComponentFactory::class);
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
        $layoutMock = $this->createMock(LayoutInterface::class);
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

        $layoutMock = $this->createMock(LayoutInterface::class);
        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);

        $layoutMock->expects($this->exactly(2))->method('getBlock');

        $this->responseMock->expects($this->once())->method('appendBody')->with('');
        $this->controller->execute();
    }
}
