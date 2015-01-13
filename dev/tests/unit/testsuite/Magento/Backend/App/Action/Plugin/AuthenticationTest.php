<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Action\Plugin;

use Magento\TestFramework\Helper\ObjectManager;

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
}
