<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App\Action\Plugin;

use Magento\Backend\App\Action\Plugin\Authentication;
use Magento\Backend\Controller\Adminhtml\Index;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * @var Auth|MockObject
     */
    protected $auth;

    /**
     * @var Authentication
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->auth = $this->createPartialMock(
            Auth::class,
            ['getUser', 'isLoggedIn', 'getAuthStorage']
        );
        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            Authentication::class,
            ['auth' => $this->auth]
        );
    }

    protected function tearDown(): void
    {
        $this->auth = null;
        $this->plugin = null;
    }

    public function testAroundDispatchProlongStorage()
    {
        $subject = $this->createMock(Index::class);
        $request = $this->createPartialMock(Http::class, ['getActionName']);
        $user = $this->createPartialMock(User::class, ['reload', '__wakeup']);
        $storage = $this->createPartialMock(Session::class, ['prolong', 'refreshAcl']);

        $expectedResult = 'expectedResult';
        $action = 'index';
        $loggedIn = true;

        $this->auth->expects($this->any())
            ->method('getUser')
            ->willReturn($user);
        $this->auth->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn($loggedIn);
        $this->auth->expects($this->any())
            ->method('getAuthStorage')
            ->willReturn($storage);

        $request->expects($this->once())
            ->method('getActionName')
            ->willReturn($action);

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
        $subject = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storage = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Stubs to control the flow of execution in aroundDispatch
        $this->auth->expects($this->any())->method('getAuthStorage')->willReturn($storage);
        $request->expects($this->once())->method('getActionName')->willReturn('non/open/action/name');
        $this->auth->expects($this->any())->method('getUser')->willReturn(false);
        $this->auth->expects($this->once())->method('isLoggedIn')->willReturn(false);
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
            } elseif ($isAjaxParam) {
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

    /**
     * @return array
     */
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
