<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Controller\Adminhtml\User;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\AuthenticationException;

/**
 * Test class for \Magento\User\Controller\Adminhtml\User\Delete testing
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\User\Controller\Adminhtml\User\Delete
     */
    private $controller;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\RequestInterface
     */
    private $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\ResponseInterface
     */
    private $responseMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Session
     */
    private $authSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\User\Model\UserFactory
     */
    private $userFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\User\Model\User
     */
    private $userMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Message\ManagerInterface
     */
    private $messageManagerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();

        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();

        $this->userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'performIdentityCheck', 'delete', 'load'])
            ->getMock();

        $this->userFactoryMock = $this->getMockBuilder(\Magento\User\Model\UserFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->controller = $objectManager->getObject(
            \Magento\User\Controller\Adminhtml\User\Delete::class,
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
            ->willReturnMap(
                [
                    ['user_id', $userId],
                    [\Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD, $currentUserPassword],
                ]
            );

        $userMock = clone $currentUserMock;

        $this->userFactoryMock->expects($this->any())->method('create')->willReturn($userMock);
        $this->responseMock->expects($this->any())->method('setRedirect')->willReturnSelf();
        $this->userMock->expects($this->any())->method('load')->with($userId)->willReturn($this->userFactoryMock);
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
            ->willReturnMap(
                [
                    ['user_id', $userId],
                    [\Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD, ''],
                ]
            );

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
                'userId'              => 2,
                'currentUserId'       => 1,
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
