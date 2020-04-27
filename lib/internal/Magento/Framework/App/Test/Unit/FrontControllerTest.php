<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Request\ValidatorInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FrontControllerTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ValidatorInterface
     */
    private $requestValidator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\MessageManager
     */
    private $messages;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\LoggerInterface
     */
    private $logger;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['isDispatched', 'setDispatched', 'initForward', 'setActionName'])
            ->getMock();

        $this->router = $this->createMock(\Magento\Framework\App\RouterInterface::class);
        $this->routerList = $this->createMock(\Magento\Framework\App\RouterList::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->requestValidator = $this->createMock(ValidatorInterface::class);
        $this->messages = $this->createMock(MessageManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->model = new \Magento\Framework\App\FrontController(
            $this->routerList,
            $this->response,
            $this->requestValidator,
            $this->messages,
            $this->logger
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
            $validCounter++;
            return $validCounter % 10 ? false : true;
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

    /**
     * Check adding validation failure message to debug log.
     */
    public function testAddingValidationFailureMessageToDebugLog()
    {
        $exceptionMessage = 'exception_message';
        $exception = new InvalidRequestException($exceptionMessage);

        $this->routerList->expects($this->any())
            ->method('valid')
            ->will($this->returnValue(true));

        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $controllerInstance = $this->getMockBuilder(\Magento\Framework\App\Action\Action::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $this->requestValidator->expects($this->once())
            ->method('validate')->with($this->request, $controllerInstance)->willThrowException($exception);
        $this->logger->expects($this->once())->method('debug')->with(
            'Request validation failed for action "'
            . get_class($controllerInstance) . '"',
            ["exception" => $exception]
        );

        $this->assertEquals($exceptionMessage, $this->model->dispatch($this->request));
    }

    public function testDispatched()
    {
        $this->routerList->expects($this->any())
            ->method('valid')
            ->will($this->returnValue(true));

        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $controllerInstance = $this->getMockBuilder(\Magento\Framework\App\Action\Action::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $controllerInstance = $this->getMockBuilder(\Magento\Framework\App\Action\Action::class)
            ->disableOriginalConstructor()
            ->getMock();
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
}
