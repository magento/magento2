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
     * @var MockObject|MessageManager
     */
    private $messages;

    /**
     * @var MockObject|LoggerInterface
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isDispatched', 'setDispatched', 'initForward', 'setActionName'])
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

    /**
     * @return void
     */
    public function testDispatchThrowException(): void
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
     *
     * @return void
     */
    public function testAddingValidationFailureMessageToDebugLog(): void
    {
        $exceptionMessage = 'exception_message';
        $exception = new InvalidRequestException($exceptionMessage);

        $this->appStateMock->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $this->areaMock
            ->method('load')
            ->withConsecutive([Area::PART_DESIGN], [Area::PART_TRANSLATE])
            ->willReturnOnConsecutiveCalls($this->areaMock, $this->areaMock);
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
        $this->router
            ->method('match')
            ->withConsecutive([$this->request], [$this->request])
            ->willReturnOnConsecutiveCalls(false, $controllerInstance);

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);

        $this->request
            ->method('isDispatched')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->request
            ->method('setDispatched')
            ->withConsecutive([true]);

        $this->requestValidator->expects($this->once())
            ->method('validate')->with($this->request, $controllerInstance)->willThrowException($exception);
        $this->logger->expects($this->once())->method('debug')->with(
            'Request validation failed for action "'
            . get_class($controllerInstance) . '"',
            ["exception" => $exception]
        );

        $this->assertEquals($exceptionMessage, $this->model->dispatch($this->request));
    }

    /**
     * @return void
     */
    public function testDispatched(): void
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
        $this->router
            ->method('match')
            ->withConsecutive([$this->request], [$this->request])
            ->willReturnOnConsecutiveCalls(false, $controllerInstance);

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);
        $this->appStateMock->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $this->areaMock
            ->method('load')
            ->withConsecutive([Area::PART_DESIGN], [Area::PART_TRANSLATE])
            ->willReturnOnConsecutiveCalls($this->areaMock, $this->areaMock);
        $this->areaListMock->expects($this->any())->method('getArea')->willReturn($this->areaMock);
        $this->request
            ->method('isDispatched')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->request
            ->method('setDispatched')
            ->with(true);

        $this->assertEquals($response, $this->model->dispatch($this->request));
    }

    /**
     * @return void
     */
    public function testDispatchedNotFoundException(): void
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
        $this->router
            ->method('match')
            ->withConsecutive([$this->request], [$this->request])
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new NotFoundException(new Phrase('Page not found.'))),
                $controllerInstance
            );

        $this->routerList->expects($this->any())
            ->method('current')
            ->willReturn($this->router);

        $this->appStateMock->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $this->areaMock
            ->method('load')
            ->withConsecutive([Area::PART_DESIGN], [Area::PART_TRANSLATE])
            ->willReturnOnConsecutiveCalls($this->areaMock, $this->areaMock);
        $this->areaListMock->expects($this->any())->method('getArea')->willReturn($this->areaMock);
        $this->request
            ->method('isDispatched')
            ->willReturnOnConsecutiveCalls(false, false, true);
        $this->request
            ->method('setDispatched')
            ->withConsecutive([false], [true]);
        $this->request
            ->method('setActionName')
            ->with('noroute');
        $this->request
            ->method('initForward');

        $this->assertEquals($response, $this->model->dispatch($this->request));
    }
}
