<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Controller\Index;

use Magento\TestFramework\Helper\ObjectManager;

class NoCookiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Controller\Index\NoCookies
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\ManagerInterface
     */
    private $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Request\Http
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Response\Http
     */
    private $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirectResponseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\ViewInterface
     */
    protected $viewMock;

    const REDIRECT_URL = 'http://www.example.com/redirect';
    const REDIRECT_PATH = '\a\path';
    const REDIRECT_ARGUMENTS = '&arg1key=arg1value';

    public function setup()
    {
        $objectManager = new ObjectManager($this);
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectResponseMock = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->getMock();
        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');

        $this->controller = $objectManager->getObject(
            'Magento\Core\Controller\Index\NoCookies',
            [
                'eventManager' => $this->eventManagerMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'redirect' => $this->redirectResponseMock,
                'view' => $this->viewMock,
            ]
        );
    }

    public function testExecuteRedirectUrl()
    {
        // redirect is new'ed in the execute function, so need to set the redirect URL in dispatch call
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('controller_action_nocookies'),
                $this->callback(
                    function ($dataArray) {
                        $redirect = $dataArray['redirect'];
                        $this->assertInstanceOf('Magento\Framework\Object', $redirect);
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
        $this->requestMock->expects($this->once())->method('setDispatched')->with($this->isTrue());

        // Make the call to test
        $this->controller->execute();
    }

    public function testExecuteRedirectPath()
    {
        // redirect is new'ed in the execute function, so need to set the redirect in dispatch call
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('controller_action_nocookies'),
                $this->callback(
                    function ($dataArray) {
                        $redirect = $dataArray['redirect'];
                        $this->assertInstanceOf('Magento\Framework\Object', $redirect);
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
            ->with($this->responseMock, $this->equalTo('\a\path'), $this->equalTo('&arg1key=arg1value'));

        // Verify request is set to dispatched
        $this->requestMock->expects($this->once())->method('setDispatched')->with($this->isTrue());

        // Make the call to test
        $this->controller->execute();
    }

    public function testExecuteDefault()
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
