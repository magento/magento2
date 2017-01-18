<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Plugin;

use Magento\Customer\Controller\Plugin\Account;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AccountTest extends \PHPUnit_Framework_TestCase
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
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var AbstractAction | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ActionFlag | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlag;

    /**
     * @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultInterface;

    protected function setUp()
    {
        $this->session = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setNoReferer',
                'unsNoReferer',
                'authenticate',
            ])
            ->getMock();

        $this->subject = $this->getMockBuilder(AbstractAction::class)
            ->setMethods([
                'getActionFlag',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getActionName',
            ])
            ->getMock();

        $this->resultInterface = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();

        $this->actionFlag = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $action
     * @param array $allowedActions
     * @param boolean $isActionAllowed
     * @param boolean $isAuthenticated
     *
     * @dataProvider beforeDispatchDataProvider
     */
    public function testBeforeDispatch(
        $action,
        $allowedActions,
        $isActionAllowed,
        $isAuthenticated
    ) {
        $this->request->expects($this->once())
            ->method('getActionName')
            ->willReturn($action);

        if ($isActionAllowed) {
            $this->session->expects($this->once())
                ->method('setNoReferer')
                ->with(true)
                ->willReturnSelf();
        } else {
            $this->session->expects($this->once())
                ->method('authenticate')
                ->willReturn($isAuthenticated);
            if (!$isAuthenticated) {
                $this->subject->expects($this->once())
                    ->method('getActionFlag')
                    ->willReturn($this->actionFlag);

                $this->actionFlag->expects($this->once())
                    ->method('set')
                    ->with('', ActionInterface::FLAG_NO_DISPATCH, true)
                    ->willReturnSelf();
            }
        }

        $plugin = new Account($this->session, $allowedActions);
        $plugin->beforeDispatch($this->subject, $this->request);
    }

    /**
     * @return array
     */
    public function beforeDispatchDataProvider()
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

    public function testAfterDispatch()
    {
        $this->session->expects($this->once())
            ->method('unsNoReferer')
            ->with(false)
            ->willReturnSelf();

        $plugin = (new ObjectManager($this))->getObject(
            Account::class,
            [
                'session' => $this->session,
                'allowedActions' => ['testaction']
            ]
        );
        $this->assertSame(
            $this->resultInterface,
            $plugin->afterDispatch($this->subject, $this->resultInterface, $this->request)
        );
    }
}
