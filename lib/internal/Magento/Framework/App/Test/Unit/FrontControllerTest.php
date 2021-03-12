<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Request\ValidatorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\RouterList;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Phrase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FrontControllerTest extends TestCase
{
    /**
     * @var FrontController
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $request;

    /**
     * @var MockObject
     */
    protected $routerList;

    /**
     * @var MockObject
     */
    protected $router;

    /**
     * @var MockObject|Http
     */
    protected $response;

    /**
     * @var MockObject|ValidatorInterface
     */
    private $requestValidator;

    /**
     * @var MockObject|\MessageManager
     */
    private $messages;

    /**
     * @var MockObject|\LoggerInterface
     */
    private $logger;

    /**
     * @var MockObject|AreaList
     */
    private $areaListMock;

    /**
     * @var MockObject|State
     */
    private $appStateMock;

    /**
     * @var MockObject|AreaInterface
     */
    private $areaMock;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['isDispatched', 'setDispatched', 'initForward', 'setActionName'])
            ->getMock();

        $this->router = $this->getMockForAbstractClass(RouterInterface::class);
        $this->routerList = $this->createMock(RouterList::class);
        $this->response = $this->createMock(Http::class);
        $this->requestValidator = $this->getMockForAbstractClass(ValidatorInterface::class);
        $this->messages = $this->createMock(MessageManager::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->appStateMock  = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->areaListMock = $this->createMock(AreaList::class);
        $this->areaMock = $this->getMockForAbstractClass(AreaInterface::class);
        $actionFlagMock = $this->createMock(ActionFlag::class);
        $eventManagerMock = $this->createMock(EventManager::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $this->model = new FrontController(
            $this->routerList,
            $this->response,
            $this->requestValidator,
            $this->messages,
            $this->logger,
            $this->appStateMock,
            $this->areaListMock,
            $actionFlagMock,
            $eventManagerMock,
            $requestMock
        );
    }

    public function testDispatchThrowException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Front controller reached 100 router match iterations');
        $validCounter = 0;
        $callbackValid = function () use (&$validCounter) {
            $validCounter++;
            return $validCounter % 10 ? false : true;
        };
        $this->routerList->expects($this->any())->method('valid')->willReturnCallback($callbackValid);

        $this->router->expects($this->any())
            ->method('match')
            ->with($this->request)
            ->willReturn(false);

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);

        $this->request->expects($this->any())->method('isDispatched')->willReturn(false);

        $this->model->dispatch($this->request);
    }

    /**
     * Check adding validation failure message to debug log.
     */
    public function testAddingValidationFailureMessageToDebugLog()
    {
        $exceptionMessage = 'exception_message';
        $exception = new InvalidRequestException($exceptionMessage);

        $this->appStateMock->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $this->areaMock->expects($this->at(0))->method('load')->with(Area::PART_DESIGN)->willReturnSelf();
        $this->areaMock->expects($this->at(1))->method('load')->with(Area::PART_TRANSLATE)->willReturnSelf();
        $this->areaListMock->expects($this->any())->method('getArea')->willReturn($this->areaMock);
        $this->routerList->expects($this->any())
            ->method('valid')
            ->willReturn(true);

        $response = $this->createMock(Http::class);
        $controllerInstance = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->getMock();
        $controllerInstance->expects($this->any())
            ->method('dispatch')
            ->with($this->request)
            ->willReturn($response);
        $this->router->expects($this->at(0))
            ->method('match')
            ->with($this->request)
            ->willReturn(false);
        $this->router->expects($this->at(1))
            ->method('match')
            ->with($this->request)
            ->willReturn($controllerInstance);

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);

        $this->request->expects($this->at(0))->method('isDispatched')->willReturn(false);
        $this->request->expects($this->at(1))->method('setDispatched')->with(true);
        $this->request->expects($this->at(2))->method('isDispatched')->willReturn(true);

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
            ->willReturn(true);

        $response = $this->createMock(Http::class);
        $controllerInstance = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->getMock();
        $controllerInstance->expects($this->any())
            ->method('dispatch')
            ->with($this->request)
            ->willReturn($response);
        $this->router->expects($this->at(0))
            ->method('match')
            ->with($this->request)
            ->willReturn(false);
        $this->router->expects($this->at(1))
            ->method('match')
            ->with($this->request)
            ->willReturn($controllerInstance);

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);
        $this->appStateMock->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $this->areaMock->expects($this->at(0))->method('load')->with(Area::PART_DESIGN)->willReturnSelf();
        $this->areaMock->expects($this->at(1))->method('load')->with(Area::PART_TRANSLATE)->willReturnSelf();
        $this->areaListMock->expects($this->any())->method('getArea')->willReturn($this->areaMock);
        $this->request->expects($this->at(0))->method('isDispatched')->willReturn(false);
        $this->request->expects($this->at(1))->method('setDispatched')->with(true);
        $this->request->expects($this->at(2))->method('isDispatched')->willReturn(true);

        $this->assertEquals($response, $this->model->dispatch($this->request));
    }

    public function testDispatchedNotFoundException()
    {
        $this->routerList->expects($this->any())
            ->method('valid')
            ->willReturn(true);

        $response = $this->createMock(Http::class);
        $controllerInstance = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->getMock();
        $controllerInstance->expects($this->any())
            ->method('dispatch')
            ->with($this->request)
            ->willReturn($response);
        $this->router->expects($this->at(0))
            ->method('match')
            ->with($this->request)
            ->willThrowException(new NotFoundException(new Phrase('Page not found.')));
        $this->router->expects($this->at(1))
            ->method('match')
            ->with($this->request)
            ->willReturn($controllerInstance);

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);

        $this->appStateMock->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $this->areaMock->expects($this->at(0))->method('load')->with(Area::PART_DESIGN)->willReturnSelf();
        $this->areaMock->expects($this->at(1))->method('load')->with(Area::PART_TRANSLATE)->willReturnSelf();
        $this->areaListMock->expects($this->any())->method('getArea')->willReturn($this->areaMock);
        $this->request->expects($this->at(0))->method('isDispatched')->willReturn(false);
        $this->request->expects($this->at(1))->method('initForward');
        $this->request->expects($this->at(2))->method('setActionName')->with('noroute');
        $this->request->expects($this->at(3))->method('setDispatched')->with(false);
        $this->request->expects($this->at(4))->method('isDispatched')->willReturn(false);
        $this->request->expects($this->at(5))->method('setDispatched')->with(true);
        $this->request->expects($this->at(6))->method('isDispatched')->willReturn(true);

        $this->assertEquals($response, $this->model->dispatch($this->request));
    }
}
