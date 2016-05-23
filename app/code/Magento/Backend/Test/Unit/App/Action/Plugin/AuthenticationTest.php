<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App\Action\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Plugin\Authentication;

/**
 * Class AuthenticationTest
 */
class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $auth;

    /**
     * @var Authentication
     */
    protected $plugin;

    protected function setUp()
    {
        $this->auth = $this->getMock(
            'Magento\Backend\Model\Auth',
            ['getUser', 'isLoggedIn', 'getAuthStorage'],
            [],
            '',
            false
        );
        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            'Magento\Backend\App\Action\Plugin\Authentication',
            ['auth' => $this->auth]
        );
    }

    protected function tearDown()
    {
        $this->auth = null;
        $this->plugin = null;
    }

    public function testAroundDispatchProlongStorage()
    {
        $subject = $this->getMock('Magento\Backend\Controller\Adminhtml\Index', [], [], '', false);
        $request = $this->getMock('\Magento\Framework\App\Request\Http', ['getActionName'], [], '', false);
        $user = $this->getMock('Magento\User\Model\User', ['reload', '__wakeup'], [], '', false);
        $storage = $this->getMock('Magento\Backend\Model\Auth\Session', ['prolong', 'refreshAcl'], [], '', false);

        $expectedResult = 'expectedResult';
        $action = 'index';
        $loggedIn = true;

        $this->auth->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->auth->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue($loggedIn));
        $this->auth->expects($this->any())
            ->method('getAuthStorage')
            ->will($this->returnValue($storage));

        $request->expects($this->once())
            ->method('getActionName')
            ->will($this->returnValue($action));

        $user->expects($this->once())
            ->method('reload');

        $storage->expects($this->at(0))
            ->method('prolong');
        $storage->expects($this->at(1))
            ->method('refreshAcl');

        $proceed = function ($request) use ($expectedResult) {
            return $expectedResult;
        };

        $this->assertEquals($expectedResult, $this->plugin->aroundDispatch($subject, $proceed, $request));
    }

    /**
     * Calls aroundDispatch to access protected method _processNotLoggedInUser
     *
     * Data provider supplies different possibilities of request parameters and properties
     * @dataProvider processNotLoggedInUserDataProvider
     */
    public function testProcessNotLoggedInUser($isIFrameParam, $isAjaxParam, $isForwardedFlag)
    {
        $subject = $this->getMockBuilder('Magento\Backend\Controller\Adminhtml\Index')
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $storage = $this->getMockBuilder('Magento\Backend\Model\Auth\Session')
            ->disableOriginalConstructor()
            ->getMock();

        // Stubs to control the flow of execution in aroundDispatch
        $this->auth->expects($this->any())->method('getAuthStorage')->will($this->returnValue($storage));
        $request->expects($this->once())->method('getActionName')->will($this->returnValue('non/open/action/name'));
        $this->auth->expects($this->any())->method('getUser')->willReturn(false);
        $this->auth->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $request->expects($this->any())->method('getPost')->willReturn(false);

        // Test cases and expectations based on provided data
        $request->expects($this->once())->method('isForwarded')->willReturn($isForwardedFlag);
        $getParamCalls = 0;
        $actionName = '';

        // If forwarded flag is set, getParam never gets called
        if (!$isForwardedFlag) {
            if ($isIFrameParam) {
                $getParamCalls = 1;
                $actionName = 'deniedIframe';
            } else if ($isAjaxParam) {
                $getParamCalls = 2;
                $actionName = 'deniedJson';
            } else {
                $getParamCalls = 2;
                $actionName = 'login';
            }
        }

        $requestParams = [
            ['isIframe', null, $isIFrameParam],
            ['isAjax', null, $isAjaxParam]
        ];

        $setterCalls = $isForwardedFlag ? 0 : 1;
        $request->expects($this->exactly($getParamCalls))->method('getParam')->willReturnMap($requestParams);
        $request->expects($this->exactly($setterCalls))->method('setForwarded')->with(true)->willReturnSelf();
        $request->expects($this->exactly($setterCalls))->method('setRouteName')->with('adminhtml')->willReturnSelf();
        $request->expects($this->exactly($setterCalls))->method('setControllerName')->with('auth')->willReturnSelf();
        $request->expects($this->exactly($setterCalls))->method('setActionName')->with($actionName)->willReturnSelf();
        $request->expects($this->exactly($setterCalls))->method('setDispatched')->with(false)->willReturnSelf();

        $expectedResult = 'expectedResult';
        $proceed = function ($request) use ($expectedResult) {
            return $expectedResult;
        };
        $this->assertEquals($expectedResult, $this->plugin->aroundDispatch($subject, $proceed, $request));
    }

    public function processNotLoggedInUserDataProvider()
    {
        return [
            'iFrame' => [true, false, false],
            'Ajax' => [false, true, false],
            'Neither iFrame nor Ajax' => [false, false, false],
            'Forwarded request' => [true, true, true]
        ];
    }
}
