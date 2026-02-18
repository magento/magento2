<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Plugin;

use Closure;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Controller\Plugin\Account;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\ResultInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class AccountTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var string
     */
    private const EXPECTED_VALUE = 'expected_value';

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

    protected function setUp(): void
    {
        $this->sessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['authenticate', 'setNoReferer', 'unsNoReferer']
        );

        $this->actionMock = $this->createPartialMockWithReflection(
            AccountInterface::class,
            ['getActionFlag', 'execute']
        );

        $this->requestMock = $this->createPartialMock(HttpRequest::class, ['getActionName']);

        $this->resultMock = $this->createMock(ResultInterface::class);

        $this->actionFlagMock = $this->createMock(ActionFlag::class);
    }

    /**
     * @param string $action
     * @param array $allowedActions
     * @param boolean $isAllowed
     * */
    #[DataProvider('beforeExecuteDataProvider')]
    public function testAroundExecuteInterruptsOriginalCallWhenNotAllowed(
        string $action,
        array $allowedActions,
        bool $isAllowed
    ) {
        /** @var callable|MockObject $proceedMock */
        $proceedMock = $this->createPartialMockWithReflection(\stdClass::class, ['__invoke']);

        $closureMock = Closure::fromCallable($proceedMock);

        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn($action);

        if ($isAllowed) {
            $proceedMock->expects($this->once())->method('__invoke')->willReturn($this->resultMock);
        } else {
            $proceedMock->expects($this->never())->method('__invoke');
        }

        $plugin = new Account($this->requestMock, $this->sessionMock, $allowedActions);
        $result = $plugin->aroundExecute($this->actionMock, $closureMock);

        if ($isAllowed) {
            $this->assertSame($this->resultMock, $result);
        } else {
            $this->assertNull($result);
        }
    }

    /**
     * @return array
     */
    public static function beforeExecuteDataProvider()
    {
        return [
            [
                'action' => 'TestAction',
                'allowedActions' => ['TestAction'],
                'isAllowed' => true
            ],
            [
                'action' => 'testaction',
                'allowedActions' => ['testaction'],
                'isAllowed' => true
            ],
            [
                'action' => 'wrongaction',
                'allowedActions' => ['testaction'],
                'isAllowed' => false
            ],
            [
                'action' => 'wrongaction',
                'allowedActions' => ['testaction'],
                'isAllowed' => false
            ],
            [
                'action' => 'wrongaction',
                'allowedActions' => [],
                'isAllowed' => false
            ],
        ];
    }
}
