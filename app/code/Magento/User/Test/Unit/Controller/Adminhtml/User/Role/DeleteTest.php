<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Controller\Adminhtml\User\Role;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for \Magento\User\Controller\Adminhtml\User\Role\Delete.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\User\Controller\Adminhtml\User\Role\Delete
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Authorization\Model\RoleFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $roleFactoryMock;

    /**
     * @var \Magento\User\Model\UserFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $userFactoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $coreRegistryMock;

    /**
     * @var \Magento\Authorization\Model\RulesFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $rulesFactoryMock;

    /**
     * @var \Magento\Backend\Model\Auth\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authSessionMock;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterManagerMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Authorization\Model\Role|\PHPUnit\Framework\MockObject\MockObject
     */
    private $roleModelMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->coreRegistryMock = $this->createPartialMock(\Magento\Framework\Registry::class, ['getId']);
        $this->roleFactoryMock = $this->createMock(\Magento\Authorization\Model\RoleFactory::class);
        $this->userFactoryMock = $this->createPartialMock(
            \Magento\User\Model\UserFactory::class,
            ['create']
        );
        $this->rulesFactoryMock = $this->createMock(\Magento\Authorization\Model\RulesFactory::class);
        $this->authSessionMock = $this->createPartialMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['getUser']
        );
        $this->filterManagerMock = $this->createMock(\Magento\Framework\Filter\FilterManager::class);
        $this->resultRedirectMock = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\Redirect::class,
            ['setPath']
        );
        $this->resultFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );

        $this->resultFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam']
        );
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $this->roleModelMock = $this->createPartialMock(
            \Magento\Authorization\Model\Role::class,
            ['load', 'getId', 'getRoleType', 'delete']
        );

        $this->controller = $objectManagerHelper->getObject(
            \Magento\User\Controller\Adminhtml\User\Role\Delete::class,
            [
                'context' => $this->contextMock,
                'coreRegistry' => $this->coreRegistryMock,
                'roleFactory' => $this->roleFactoryMock,
                'userFactory' => $this->userFactoryMock,
                'rulesFactory' => $this->rulesFactoryMock,
                'authSession' => $this->authSessionMock,
                'filterManager' => $this->filterManagerMock,
            ]
        );
    }

    /**
     * Unit test which trying to delete role which assigned on current user.
     *
     * @return void
     */
    public function testExecuteDeleteSelfAssignedRole()
    {
        $idUser = 1;
        $idUserRole = 3;
        $idDeleteRole = 3;

        $this->checkUserAndRoleIds($idDeleteRole, $idUser, $idUserRole);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('You cannot delete self-assigned roles.'))
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/editrole', ['rid' => $idDeleteRole])
            ->willReturnSelf();

        $this->controller->execute();
    }

    /**
     * Unit test which trying to delete role.
     *
     * @return void
     */
    public function testExecuteDeleteWithNormalScenario()
    {
        $idUser = 1;
        $idUserRole = 3;
        $idDeleteRole = 5;
        $roleType = 'G';

        $this->checkUserAndRoleIds($idDeleteRole, $idUser, $idUserRole);

        $this->initRoleExecute($roleType);
        $this->roleModelMock->expects($this->exactly(2))->method('getId')->willReturn($idDeleteRole);

        $this->roleModelMock->expects($this->once())->method('delete')->willReturnSelf();

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You deleted the role.'))
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->controller->execute();
    }

    /**
     * Unit test which failed on delete role.
     *
     * @return void
     */
    public function testExecuteDeleteWithError()
    {
        $idUser = 1;
        $idUserRole = 3;
        $idDeleteRole = 5;
        $roleType = 'G';

        $this->checkUserAndRoleIds($idDeleteRole, $idUser, $idUserRole);

        $this->initRoleExecute($roleType);
        $this->roleModelMock->expects($this->exactly(2))->method('getId')->willReturn($idDeleteRole);

        $this->roleModelMock->expects($this->once())->method('delete')->willThrowException(new \Exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('An error occurred while deleting this role.'))
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->controller->execute();
    }

    /**
     * Unit test which trying to delete nonexistent role.
     *
     * @return void
     */
    public function testExecuteWithoutRole()
    {
        $idUser = 1;
        $idUserRole = 3;
        $idDeleteRole = 100;
        $roleType = null;

        $this->checkUserAndRoleIds($idDeleteRole, $idUser, $idUserRole);

        $this->initRoleExecute($roleType);
        $this->roleModelMock->expects($this->at(1))->method('getId')->willReturn($idDeleteRole);
        $this->roleModelMock->expects($this->at(2))->method('getId')->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('We can\'t find a role to delete.'))
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->controller->execute();
    }

    /**
     * Method which takes Id from request and check with User Role Id.
     *
     * @param int $id
     * @param int $userId
     * @param int $userRoleId
     * @return void
     */
    private function checkUserAndRoleIds(int $id, int $userId, int $userRoleId)
    {
        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->with('rid')->willReturn($id);

        $userModelMock = $this->createPartialMock(\Magento\User\Model\User::class, ['getId', 'setId', 'getRoles']);
        $this->authSessionMock->expects($this->once())->method('getUser')->willReturn($userModelMock);
        $userModelMock->expects($this->once())->method('getId')->willReturn($userId);

        $this->userFactoryMock->expects($this->once())->method('create')->willReturn($userModelMock);
        $userModelMock->expects($this->once())->method('setId')->with($userId)->willReturnSelf();

        $userModelMock->expects($this->once())->method('getRoles')->willReturn(['id' => $userRoleId]);
    }

    /**
     * Execute initialization Role.
     *
     * @param string|null $roleType
     * @return void
     */
    private function initRoleExecute($roleType)
    {
        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($this->roleModelMock);
        $this->roleModelMock->expects($this->once())->method('load')->willReturnSelf();
        $this->roleModelMock->expects($this->once())->method('getRoleType')->willReturn($roleType);
    }
}
