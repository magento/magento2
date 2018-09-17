<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Plugin;

use Magento\Customer\Controller\Plugin\Account;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;

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
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var ActionInterface | \PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->session = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([
                'setNoReferer',
                'unsNoReferer',
                'authenticate',
            ])
            ->getMock();

        $this->subject = $this->getMockBuilder('Magento\Framework\App\ActionInterface')
            ->setMethods([
                'getActionFlag',
            ])
            ->getMockForAbstractClass();

        $this->proceed = function () {
            return self::EXPECTED_VALUE;
        };

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods([
                'getActionName',
            ])
            ->getMock();

        $this->actionFlag = $this->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $action
     * @param array $allowedActions
     * @param boolean $isActionAllowed
     * @param boolean $isAuthenticated
     *
     * @dataProvider dataProviderAroundDispatch
     */
    public function testAroundDispatch(
        $action,
        $allowedActions,
        $isActionAllowed,
        $isAuthenticated
    ) {
        $this->request->expects($this->once())
            ->method('getActionName')
            ->willReturn($action);

        $this->session->expects($this->once())
            ->method('unsNoReferer')
            ->with(false)
            ->willReturnSelf();

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
        $this->assertEquals(
            self::EXPECTED_VALUE,
            $plugin->aroundDispatch($this->subject, $this->proceed, $this->request)
        );
    }

    /**
     * @return array
     */
    public function dataProviderAroundDispatch()
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
}
