<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cookie\Test\Unit\Controller\Index;

use Magento\Cookie\Controller\Index\NoCookies;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cookie\Controller\Index\NoCookies
 */
class NoCookiesTest extends TestCase
{
    /**
     * @var NoCookies
     */
    private $controller;

    /**
     * @var MockObject|ManagerInterface
     */
    private $eventManagerMock;

    /**
     * @var MockObject|HttpRequest
     */
    private $requestMock;

    /**
     * @var MockObject|HttpResponse
     */
    private $responseMock;

    /**
     * @var MockObject|RedirectInterface
     */
    private $redirectResponseMock;

    /**
     * @var MockObject|ViewInterface
     */
    private $viewMock;

    const REDIRECT_URL = 'http://www.example.com/redirect';
    const REDIRECT_PATH = '\a\path';
    const REDIRECT_ARGUMENTS = '&arg1key=arg1value';

    /**
     * @inheritDoc
     */
    protected function setup(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(HttpResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectResponseMock = $this->getMockBuilder(RedirectInterface::class)
            ->getMock();
        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->controller = $objectManager->getObject(
            NoCookies::class,
            [
                'eventManager' => $this->eventManagerMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'redirect' => $this->redirectResponseMock,
                'view' => $this->viewMock,
            ]
        );
    }

    /**
     * Test execute redirect url
     */
    public function testExecuteRedirectUrl(): void
    {
        // redirect is new'ed in the execute function, so need to set the redirect URL in dispatch call
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'controller_action_nocookies',
                $this->callback(
                    function ($dataArray) {
                        $redirect = $dataArray['redirect'];
                        $this->assertInstanceOf(DataObject::class, $redirect);
                        $redirect->setRedirectUrl(self::REDIRECT_URL);
                        return true;
                    }
                )
            );

        // Verify response is set with redirect url
        $this->responseMock->expects($this->once())
            ->method('setRedirect')
            ->with(self::REDIRECT_URL);

        // Verify request is set to dispatched
        $this->requestMock->expects($this->once())
            ->method('setDispatched')
            ->with($this->isTrue());

        // Make the call to test
        $this->controller->execute();
    }

    /**
     * Test execute redirect path
     */
    public function testExecuteRedirectPath(): void
    {
        // redirect is new'ed in the execute function, so need to set the redirect in dispatch call
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'controller_action_nocookies',
                $this->callback(
                    function ($dataArray) {
                        $redirect = $dataArray['redirect'];
                        $this->assertInstanceOf(DataObject::class, $redirect);
                        $redirect->setArguments(self::REDIRECT_ARGUMENTS);
                        $redirect->setPath(self::REDIRECT_PATH);
                        $redirect->setRedirect(self::REDIRECT_URL);
                        return true;
                    }
                )
            );

        // Verify response is set with redirect, which
        $this->redirectResponseMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, '\a\path', '&arg1key=arg1value');

        // Verify request is set to dispatched
        $this->requestMock->expects($this->once())->method('setDispatched')->with($this->isTrue());

        // Make the call to test
        $this->controller->execute();
    }

    /**
     * Test execute default
     */
    public function testExecuteDefault(): void
    {
        // Verify view is called to load and render
        $this->viewMock->expects($this->once())->method('loadLayout')->with(['default', 'noCookie']);
        $this->viewMock->expects($this->once())->method('renderLayout');

        // Verify request is set to dispatched
        $this->requestMock->expects($this->once())->method('setDispatched')->with($this->isTrue());

        // Make the call to test
        $this->controller->execute();
    }
}
