<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Controller\Adminhtml\User;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\User\Block\User\Edit\Tab\Main;
use Magento\User\Controller\Adminhtml\User\Delete;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\User\Controller\Adminhtml\User\Delete testing
 */
class DeleteTest extends TestCase
{
    /**
     * @var Delete
     */
    private $controller;

    /**
     * @var MockObject|RequestInterface
     */
    private $requestMock;

    /**
     * @var MockObject|ResponseInterface
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
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();

        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();

        $this->userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'performIdentityCheck', 'delete'])
            ->getMock();

        $this->userFactoryMock = $this->getMockBuilder(UserFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->controller = $objectManager->getObject(
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
     * @param int    $userId
     * @param int    $currentUserId
     * @param string $resultMethod
     *
     * @dataProvider executeDataProvider
     * @return void
     *
     */
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
            ->willReturnMap([
                ['user_id', $userId],
                [Main::CURRENT_USER_PASSWORD_FIELD, $currentUserPassword],
            ]);

        $userMock = clone $currentUserMock;

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
    public function executeDataProvider()
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
