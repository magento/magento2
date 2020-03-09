<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Plugin;

use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Controller\Plugin\Account;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    /**
     * @var string
     */
    const EXPECTED_VALUE = 'expected_value';

    /**
     * @var Account
     */
    protected $plugin;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var AccountInterface|MockObject
     */
    protected $actionMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var ActionFlag|MockObject
     */
    protected $actionFlagMock;

    /**
     * @var ResultInterface|MockObject
     */
    private $resultMock;

    protected function setUp()
    {
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setNoReferer', 'unsNoReferer', 'authenticate'])
            ->getMock();

        $this->actionMock = $this->getMockBuilder(AccountInterface::class)
            ->setMethods(['getActionFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActionName'])
            ->getMock();

        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();

        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $action
     * @param array $allowedActions
     * @param boolean $isActionAllowed
     * @param boolean $isAuthenticated
     *
     * @dataProvider beforeExecuteDataProvider
     */
    public function testBeforeExecute($action, $allowedActions, $isActionAllowed, $isAuthenticated)
    {
        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn($action);

        if ($isActionAllowed) {
            $this->sessionMock->expects($this->once())
                ->method('setNoReferer')
                ->with(true)
                ->willReturnSelf();
        } else {
            $this->sessionMock->expects($this->once())
                ->method('authenticate')
                ->willReturn($isAuthenticated);
            if (!$isAuthenticated) {
                $this->actionMock->expects($this->once())
                    ->method('getActionFlag')
                    ->willReturn($this->actionFlagMock);

                $this->actionFlagMock->expects($this->once())
                    ->method('set')
                    ->with('', ActionInterface::FLAG_NO_DISPATCH, true)
                    ->willReturnSelf();
            }
        }

        $plugin = new Account($this->requestMock, $this->sessionMock, $allowedActions);
        $plugin->beforeExecute($this->actionMock);
    }

    /**
     * @return array
     */
    public function beforeExecuteDataProvider()
    {
        return [
            [
                'action' => 'TestAction',
                'allowed_actions' => ['TestAction'],
                'is_action_allowed' => 1,
                'is_authenticated' => 0,
            ],
            [
                'action' => 'testaction',
                'allowed_actions' => ['testaction'],
                'is_action_allowed' => 1,
                'is_authenticated' => 0,
            ],
            [
                'action' => 'wrongaction',
                'allowed_actions' => ['testaction'],
                'is_action_allowed' => 0,
                'is_authenticated' => 0,
            ],
            [
                'action' => 'wrongaction',
                'allowed_actions' => ['testaction'],
                'is_action_allowed' => 0,
                'is_authenticated' => 1,
            ],
            [
                'action' => 'wrongaction',
                'allowed_actions' => [],
                'is_action_allowed' => 0,
                'is_authenticated' => 1,
            ],
        ];
    }

    public function testAfterExecute()
    {
        $this->sessionMock->expects($this->once())
            ->method('unsNoReferer')
            ->with(false)
            ->willReturnSelf();

        $plugin = (new ObjectManager($this))->getObject(
            Account::class,
            [
                'session' => $this->sessionMock,
                'allowedActions' => ['testaction']
            ]
        );
        $this->assertSame(
            $this->resultMock,
            $plugin->afterExecute($this->actionMock, $this->resultMock, $this->requestMock)
        );
    }
}
