<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Action;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Action\ActionFake */
    protected $action;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_actionFlagMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_redirectMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * Full action name
     */
    const FULL_ACTION_NAME = 'module/controller/someaction';

    /**
     * Route name
     */
    const ROUTE_NAME = 'module/controller/actionroute';

    /**
     * Action name
     */
    const ACTION_NAME = 'someaction';

    /**
     * Controller name
     */
    const CONTROLLER_NAME = 'controller';

    /**
     * Module name
     */
    const MODULE_NAME = 'module';

    public static $actionParams = ['param' => 'value'];

    protected function setUp()
    {
        $this->_eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->_actionFlagMock = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $this->_redirectMock = $this->getMock('Magento\Framework\App\Response\RedirectInterface', [], [], '', false);
        $this->_requestMock = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            [
                'getFullActionName',
                'getRouteName',
                'isDispatched',
                'initForward',
                'setParams',
                'setControllerName',
                'setDispatched',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'getCookie'
            ],
            [],
            '',
            false
        );
        $this->_responseMock = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);

        $this->pageConfigMock = $this->getMock('Magento\Framework\View\Page\Config', ['getConfig'], [], '', false);
        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->viewMock->expects($this->any())->method('getPage')->will($this->returnValue($this->pageConfigMock));
        $this->pageConfigMock->expects($this->any())->method('getConfig')->will($this->returnValue(1));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->action = $this->objectManagerHelper->getObject(
            'Magento\Framework\App\Action\ActionFake',
            [
                'request' => $this->_requestMock,
                'response' => $this->_responseMock,
                'eventManager' => $this->_eventManagerMock,
                'redirect' => $this->_redirectMock,
                'actionFlag' => $this->_actionFlagMock,
                'view' => $this->viewMock,
            ]
        );
        \Magento\Framework\Profiler::disable();
    }

    public function testDispatchPostDispatch()
    {
        $this->_requestMock->expects($this->exactly(3))->method('getFullActionName')->will(
            $this->returnValue(self::FULL_ACTION_NAME)
        );
        $this->_requestMock->expects($this->exactly(2))->method('getRouteName')->will(
            $this->returnValue(self::ROUTE_NAME)
        );
        $expectedEventParameters = ['controller_action' => $this->action, 'request' => $this->_requestMock];
        $this->_eventManagerMock->expects($this->at(0))->method('dispatch')->with(
            'controller_action_predispatch',
            $expectedEventParameters
        );
        $this->_eventManagerMock->expects($this->at(1))->method('dispatch')->with(
            'controller_action_predispatch_' . self::ROUTE_NAME,
            $expectedEventParameters
        );
        $this->_eventManagerMock->expects($this->at(2))->method('dispatch')->with(
            'controller_action_predispatch_' . self::FULL_ACTION_NAME,
            $expectedEventParameters
        );

        $this->_requestMock->expects($this->once())->method('isDispatched')->will($this->returnValue(true));
        $this->_actionFlagMock->expects($this->at(0))->method('get')->with('', Action::FLAG_NO_DISPATCH)->will(
            $this->returnValue(false)
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

        $this->_actionFlagMock->expects($this->at(1))->method('get')->with('', Action::FLAG_NO_POST_DISPATCH)->will(
            $this->returnValue(false)
        );

        $this->_eventManagerMock->expects($this->at(3))->method('dispatch')->with(
            'controller_action_postdispatch_' . self::FULL_ACTION_NAME,
            $expectedEventParameters
        );
        $this->_eventManagerMock->expects($this->at(4))->method('dispatch')->with(
            'controller_action_postdispatch_' . self::ROUTE_NAME,
            $expectedEventParameters
        );
        $this->_eventManagerMock->expects($this->at(5))->method('dispatch')->with(
            'controller_action_postdispatch',
            $expectedEventParameters
        );

        $this->assertEquals($this->_responseMock, $this->action->dispatch($this->_requestMock));
    }
}

class ActionFake extends Action
{
    /**
     * Fake action to check a method call from a parent
     */
    public function execute()
    {
        $this->_forward(
            ActionTest::ACTION_NAME,
            ActionTest::CONTROLLER_NAME,
            ActionTest::MODULE_NAME,
            ActionTest::$actionParams);
        $this->_redirect(ActionTest::FULL_ACTION_NAME, ActionTest::$actionParams);
        return;
    }
}
