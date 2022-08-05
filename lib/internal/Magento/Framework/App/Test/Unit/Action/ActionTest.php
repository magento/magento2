<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Action;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http as HttpRequest;

use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Profiler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Page\Config as PageConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{
    /**
     * @var ActionFake
     */
    protected $action;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var HttpRequest|MockObject
     */
    protected $_requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $_responseMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var ActionFlag|MockObject
     */
    protected $_actionFlagMock;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $_redirectMock;

    /**
     * @var ViewInterface|MockObject
     */
    protected $viewMock;

    /**
     * @var PageConfig|MockObject
     */
    protected $pageConfigMock;

    public const FULL_ACTION_NAME = 'module/controller/someaction';
    public const ROUTE_NAME = 'module/controller/actionroute';
    public const ACTION_NAME = 'someaction';
    public const CONTROLLER_NAME = 'controller';
    public const MODULE_NAME = 'module';

    public static $actionParams = ['param' => 'value'];

    protected function setUp(): void
    {
        $this->_eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->_actionFlagMock = $this->createMock(ActionFlag::class);
        $this->_redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->_requestMock = $this->createMock(HttpRequest::class);
        $this->_responseMock = $this->getMockForAbstractClass(ResponseInterface::class);

        $this->pageConfigMock = $this->getMockBuilder(PageConfig::class)
            ->addMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->viewMock->expects($this->any())->method('getPage')->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())->method('getConfig')->willReturn(1);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->action = $this->objectManagerHelper->getObject(
            ActionFake::class,
            [
                'request' => $this->_requestMock,
                'response' => $this->_responseMock,
                'eventManager' => $this->_eventManagerMock,
                'redirect' => $this->_redirectMock,
                'actionFlag' => $this->_actionFlagMock,
                'view' => $this->viewMock,
            ]
        );
        Profiler::disable();
    }

    public function testDispatchPostDispatch()
    {
        $this->_requestMock->method('getFullActionName')->willReturn(self::FULL_ACTION_NAME);
        $this->_requestMock->method('getRouteName')->willReturn(self::ROUTE_NAME);
        $this->_requestMock->method('isDispatched')->willReturn(true);
        $this->_actionFlagMock->method('get')->willReturnMap(
            ['', Action::FLAG_NO_DISPATCH, false],
            ['', Action::FLAG_NO_POST_DISPATCH]
        );

        // _forward expectations
        $this->_requestMock->expects($this->once())->method('initForward');
        $this->_requestMock->expects($this->once())->method('setParams')->with(self::$actionParams);
        $this->_requestMock->expects($this->once())->method('setControllerName')->with(self::CONTROLLER_NAME);
        $this->_requestMock->expects($this->once())->method('setModuleName')->with(self::MODULE_NAME);
        $this->_requestMock->expects($this->once())->method('setActionName')->with(self::ACTION_NAME);
        $this->_requestMock->expects($this->once())->method('setDispatched')->with(false);

        // _redirect expectations
        $this->_redirectMock->expects($this->once())->method('redirect')->with(
            $this->_responseMock,
            self::FULL_ACTION_NAME,
            self::$actionParams
        );

        $this->assertEquals($this->_responseMock, $this->action->dispatch($this->_requestMock));
    }
}
