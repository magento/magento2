<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Controller\Adminhtml\User;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\User\Controller\Adminhtml\User\Save;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveTest extends TestCase
{
    /**
     * @var Save|MockObject
     */
    private $controller;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var UserFactory|MockObject
     */
    private $userFactoryMock;

    /**
     * @var User|MockObject
     */
    private $userModelMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->userFactoryMock = $this->createPartialMock(UserFactory::class, ['create']);
        $this->userModelMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isObjectNew', 'load', 'setData', 'validate'])
            ->addMethods(['setRoleId'])
            ->getMock();
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setUserData'])
            ->getMock();
        // // @phpcsSuppress Magento2.Security.GlobalState
        $registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->userFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->userModelMock);
        $responseMock = $this->createMock(ResponseInterface::class);
        $redirectMock = $this->createMock(RedirectInterface::class);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($responseMock);
        $this->contextMock->expects($this->once())
            ->method('getRedirect')
            ->willReturn($redirectMock);
        $this->contextMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->controller = $this->getMockBuilder(Save::class)
            ->setConstructorArgs([
                'context' => $this->contextMock,
                'userFactory' => $this->userFactoryMock,
                'coreRegistry' => $registryMock
            ])
            ->onlyMethods(['redirectToEdit'])
            ->getMock();
    }

    public function testExecuteValidationFailure()
    {
        $userId = 1;
        $postData = ['username' => 'testuser'];
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('user_id')
            ->willReturn($userId);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);
        $this->userModelMock->expects($this->once())
            ->method('load')
            ->with($userId)
            ->willReturnSelf();
        $this->userModelMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(false);
        $this->userModelMock->expects($this->once())
            ->method('setData')
            ->willReturnSelf();
        $this->userModelMock->expects($this->once())
            ->method('validate')
            ->willReturn(['Validation error message']);
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Validation error message');
        $this->sessionMock->expects($this->once())
            ->method('setUserData')
            ->with($postData);
        $this->controller->expects($this->once())
            ->method('redirectToEdit')
            ->with($this->userModelMock, $postData)
            ->will($this->returnCallback(function () use ($postData) {
                $this->sessionMock->setUserData($postData);
            }));

        $this->controller->execute();
    }
}
