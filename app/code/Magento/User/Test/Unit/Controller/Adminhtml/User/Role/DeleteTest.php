<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Controller\Adminhtml\User\Role;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Message\Manager;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\User\Controller\Adminhtml\User\Role\Delete;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\User\Controller\Adminhtml\User\Role\Delete.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends TestCase
{
    /**
     * @var Delete
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var RoleFactory|MockObject
     */
    private $roleFactoryMock;

    /**
     * @var UserFactory|MockObject
     */
    private $userFactoryMock;

    /**
     * @var Registry|MockObject
     */
    private $coreRegistryMock;

    /**
     * @var RulesFactory|MockObject
     */
    private $rulesFactoryMock;

    /**
     * @var Session|MockObject
     */
    private $authSessionMock;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManagerMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Manager|MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Authorization\Model\Role|MockObject
     */
    private $roleModelMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->createMock(Context::class);
        $this->coreRegistryMock = $this->getMockBuilder(Registry::class)
            ->addMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->roleFactoryMock = $this->createMock(RoleFactory::class);
        $this->userFactoryMock = $this->createPartialMock(
            UserFactory::class,
            ['create']
        );
        $this->rulesFactoryMock = $this->createMock(RulesFactory::class);
        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterManagerMock = $this->createMock(FilterManager::class);
        $this->resultRedirectMock = $this->createPartialMock(
            Redirect::class,
            ['setPath']
        );
        $this->resultFactoryMock = $this->createPartialMock(
            ResultFactory::class,
            ['create']
        );

        $this->resultFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
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
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);

        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $this->roleModelMock = $this->getMockBuilder(Role::class)
            ->addMethods(['getRoleType'])
            ->onlyMethods(['load', 'getId', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManagerHelper->getObject(
            Delete::class,
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

        $this->roleModelMock->expects($this->once())->method('delete')->willThrowException(new \Exception());

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

        $userModelMock = $this->createPartialMock(User::class, ['getId', 'setId', 'getRoles']);
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
