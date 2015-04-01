<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\State;

class FrontControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\FrontController
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $routerList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['isDispatched', 'setDispatched', 'initForward', 'setActionName'])
            ->getMock();

        $this->router = $this->getMock('Magento\Framework\App\RouterInterface');
        $this->routerList = $this->getMock('Magento\Framework\App\RouterList', [], [], '', false);
        $this->messageManager = $this->getMock('Magento\Framework\Message\ManagerInterface', [], [], '', false);
        $this->logger = $this->getMock('Psr\Log\LoggerInterface', [], [], '', false);
        $this->appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                'Magento\Framework\App\FrontController',
                [
                    'routerList' => $this->routerList,
                    'messageManager' => $this->messageManager,
                    'logger' => $this->logger,
                    'appState' => $this->appState
                ]
            );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage  Front controller reached 100 router match iterations
     */
    public function testDispatchThrowException()
    {
        $validCounter = 0;
        $callbackValid = function () use (&$validCounter) {
            return $validCounter++%10 ? false : true;
        };
        $this->routerList->expects($this->any())->method('valid')->will($this->returnCallback($callbackValid));

        $this->router->expects($this->any())
            ->method('match')
            ->with($this->request)
            ->will($this->returnValue(false));

        $this->routerList->expects($this->any())
            ->method('current')
            ->will($this->returnValue($this->router));

        $this->request->expects($this->any())->method('isDispatched')->will($this->returnValue(false));

        $this->model->dispatch($this->request);
    }

    public function testDispatched()
    {
        $this->routerList->expects($this->any())
            ->method('valid')
            ->will($this->returnValue(true));

        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $controllerInstance = $this->getMock('Magento\Framework\App\ActionInterface');
        $controllerInstance->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $controllerInstance->expects($this->any())
            ->method('dispatch')
            ->with($this->request)
            ->will($this->returnValue($response));
        $this->router->expects($this->at(0))
            ->method('match')
            ->with($this->request)
            ->will($this->returnValue(false));
        $this->router->expects($this->at(1))
            ->method('match')
            ->with($this->request)
            ->will($this->returnValue($controllerInstance));

        $this->routerList->expects($this->any())
            ->method('current')
            ->will($this->returnValue($this->router));

        $this->request->expects($this->at(0))->method('isDispatched')->will($this->returnValue(false));
        $this->request->expects($this->at(1))->method('setDispatched')->with(true);
        $this->request->expects($this->at(2))->method('isDispatched')->will($this->returnValue(true));

        $this->assertEquals($response, $this->model->dispatch($this->request));
    }

    public function testDispatchedNotFoundException()
    {
        $this->routerList->expects($this->any())
            ->method('valid')
            ->will($this->returnValue(true));

        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $controllerInstance = $this->getMock('Magento\Framework\App\ActionInterface');
        $controllerInstance->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $controllerInstance->expects($this->any())
            ->method('dispatch')
            ->with($this->request)
            ->will($this->returnValue($response));
        $this->router->expects($this->at(0))
            ->method('match')
            ->with($this->request)
            ->willThrowException(new NotFoundException(new \Magento\Framework\Phrase('Page not found.')));
        $this->router->expects($this->at(1))
            ->method('match')
            ->with($this->request)
            ->will($this->returnValue($controllerInstance));

        $this->routerList->expects($this->any())
            ->method('current')
            ->will($this->returnValue($this->router));

        $this->request->expects($this->at(0))->method('isDispatched')->will($this->returnValue(false));
        $this->request->expects($this->at(1))->method('initForward');
        $this->request->expects($this->at(2))->method('setActionName')->with('noroute');
        $this->request->expects($this->at(3))->method('setDispatched')->with(false);
        $this->request->expects($this->at(4))->method('isDispatched')->will($this->returnValue(false));
        $this->request->expects($this->at(5))->method('setDispatched')->with(true);
        $this->request->expects($this->at(6))->method('isDispatched')->will($this->returnValue(true));

        $this->assertEquals($response, $this->model->dispatch($this->request));
    }

    public function testDispatchedLocalizedException()
    {
        $message = 'Test';
        $this->routerList->expects($this->any())
            ->method('valid')
            ->willReturn(true);

        $this->resultRedirect = $this->getMock('Magento\Framework\Controller\Result\Redirect', [], [], '', false);

        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $controllerInstance = $this->getMock('Magento\Framework\App\ActionInterface');
        $controllerInstance->expects($this->any())
            ->method('getResponse')
            ->willReturn($response);
        $controllerInstance->expects($this->any())
            ->method('dispatch')
            ->with($this->request)
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase($message))
            );
        $controllerInstance->expects($this->once())->method('getDefaultResult')->willReturn($this->resultRedirect);

        $this->router->expects($this->once())
            ->method('match')
            ->with($this->request)
            ->willReturn($controllerInstance);

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);

        $this->request->expects($this->at(0))->method('isDispatched')->willReturn(false);
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->request->expects($this->at(2))->method('isDispatched')->willReturn(true);

        $this->messageManager->expects($this->once())->method('addError')->with($message);
        $this->logger->expects($this->once())->method('critical')->with($message);

        $this->assertEquals($this->resultRedirect, $this->model->dispatch($this->request));
    }

    /**
     * @param string $mode
     * @param string $exceptionMessage
     * @param string $sessionMessage
     * @dataProvider dispatchedWithPhpExceptionDataProvider
     */
    public function testDispatchedPhpException($mode, $exceptionMessage, $sessionMessage)
    {
        $this->routerList->expects($this->any())
            ->method('valid')
            ->willReturn(true);

        $this->resultRedirect = $this->getMock('Magento\Framework\Controller\Result\Redirect', [], [], '', false);

        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $controllerInstance = $this->getMock('Magento\Framework\App\ActionInterface');
        $controllerInstance->expects($this->any())
            ->method('getResponse')
            ->willReturn($response);
        $controllerInstance->expects($this->any())
            ->method('dispatch')
            ->with($this->request)
            ->willThrowException(new \Exception(new \Magento\Framework\Phrase($exceptionMessage)));
        $controllerInstance->expects($this->once())->method('getDefaultResult')->willReturn($this->resultRedirect);

        $this->router->expects($this->once())
            ->method('match')
            ->with($this->request)
            ->willReturn($controllerInstance);

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);

        $this->request->expects($this->at(0))->method('isDispatched')->willReturn(false);
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->request->expects($this->at(2))->method('isDispatched')->willReturn(true);

        $this->appState->expects($this->once())->method('getMode')->willReturn($mode);

        $this->messageManager->expects($this->once())->method('addError')->with($sessionMessage);
        $this->logger->expects($this->once())->method('critical')->with($exceptionMessage);

        $this->assertEquals($this->resultRedirect, $this->model->dispatch($this->request));
    }

    /**
     * @return array
     */
    public function dispatchedWithPhpExceptionDataProvider()
    {
        return [
            [State::MODE_DEVELOPER, 'Test', 'Test'],
            [State::MODE_DEFAULT, 'Test', 'An error occurred while processing your request'],
            [State::MODE_PRODUCTION, 'Test', 'An error occurred while processing your request'],
        ];
    }
}
