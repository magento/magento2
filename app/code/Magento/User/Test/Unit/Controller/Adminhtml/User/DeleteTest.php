<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Controller\Adminhtml\User;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\User\Block\User\Edit\Tab\Main;
use Magento\User\Controller\Adminhtml\User\Delete;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\User\Controller\Adminhtml\User\Delete testing
 */
class DeleteTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Delete
     */
    private $controller;

    /**
     * @var MockObject|HttpRequest
     */
    private $requestMock;

    /**
     * @var MockObject|HttpInterface
     */
    private $responseMock;

    /**
     * @var MockObject|Session
     */
    private $authSessionMock;

    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManagerMock;

    /**
     * @var MockObject|UserFactory
     */
    private $userFactoryMock;

    /**
     * @var MockObject|User
     */
    private $userMock;

    /**
     * @var MockObject|ManagerInterface
     */
    private $messageManagerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->objectManagerMock = $this->createPartialMock(
            ObjectManager::class,
            ['get', 'create']
        );

        $this->responseMock = $this->createMock(HttpInterface::class);

        $this->requestMock = $this->createPartialMock(
            HttpRequest::class,
            ['getPost']
        );

        $this->authSessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['getUser']
        );

        $this->userMock = $this->createPartialMock(
            User::class,
            ['getId', 'performIdentityCheck', 'delete', 'load']
        );

        $this->userFactoryMock = $this->createPartialMock(
            UserFactory::class,
            ['create']
        );

        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->controller = $objectManagerHelper->getObject(
            Delete::class,
            [
                'request'        => $this->requestMock,
                'response'       => $this->responseMock,
                'objectManager'  => $this->objectManagerMock,
                'messageManager' => $this->messageManagerMock,
                'userFactory'  => $this->userFactoryMock,
            ]
        );
    }

    /**
     * Test method \Magento\User\Controller\Adminhtml\User\Delete::execute
     *
     * @param string $currentUserPassword
     * @param int $userId
     * @param int $currentUserId
     * @param string $resultMethod
     * @return void
     */
    #[DataProvider('executeDataProvider')]
    public function testExecute($currentUserPassword, $userId, $currentUserId, $resultMethod)
    {
        $currentUserMock = $this->userMock;
        $this->authSessionMock->expects($this->any())->method('getUser')->willReturn($currentUserMock);

        $currentUserMock->expects($this->any())->method('getId')->willReturn($currentUserId);

        $this->objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->authSessionMock);

        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->willReturnCallback(function ($key) use ($userId, $currentUserPassword) {
                if ($key === 'user_id') {
                    return $userId;
                }
                if ($key === Main::CURRENT_USER_PASSWORD_FIELD) {
                    return $currentUserPassword;
                }
                return null;
            });

        $userMock = clone $currentUserMock;

        if ($resultMethod === 'addSuccess' && $userId && $currentUserId !== $userId) {
            $currentUserMock->expects($this->once())
                ->method('performIdentityCheck')
                ->with($currentUserPassword)
                ->willReturn(true);
        }

        $this->userFactoryMock->expects($this->any())->method('create')->willReturn($userMock);
        $this->responseMock->expects($this->any())->method('setRedirect')->willReturnSelf();
        $this->userMock->expects($this->any())->method('delete')->willReturnSelf();
        $this->messageManagerMock->expects($this->once())->method($resultMethod);

        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testEmptyPassword()
    {
        $currentUserId = 1;
        $userId = 2;

        $currentUserMock = $this->userMock;
        $this->authSessionMock->expects($this->any())
            ->method('getUser')
            ->willReturn($currentUserMock);

        $currentUserMock->expects($this->any())->method('getId')->willReturn($currentUserId);

        $this->objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->authSessionMock);

        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->willReturnMap([
                ['user_id', $userId],
                [Main::CURRENT_USER_PASSWORD_FIELD, ''],
            ]);

        $result = $this->controller->execute();
        $this->assertNull($result);
    }

    /**
     * Data Provider for execute method
     *
     * @return array
     */
    public static function executeDataProvider()
    {
        return [
            [
                'currentUserPassword' => '123123q',
                'userId'              => 1,
                'currentUserId'       => 2,
                'resultMethod'        => 'addSuccess',
            ],
            [
                'currentUserPassword' => '123123q',
                'userId'              => 0,
                'currentUserId'       => 2,
                'resultMethod'        => 'addError',
            ],
            [
                'currentUserPassword' => '123123q',
                'userId'              => 1,
                'currentUserId'       => 1,
                'resultMethod'        => 'addError',
            ],
        ];
    }
}
