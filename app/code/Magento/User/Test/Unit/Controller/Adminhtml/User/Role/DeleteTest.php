<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Controller\Adminhtml\User\Role;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for \Magento\User\Controller\Adminhtml\User\Role\Delete.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\User\Controller\Adminhtml\User\Role\Delete
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Authorization\Model\RoleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleFactoryMock;

    /**
     * @var \Magento\User\Model\UserFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userFactoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistryMock;

    /**
     * @var \Magento\Authorization\Model\RulesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rulesFactoryMock;

    /**
     * @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authSessionMock;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterManagerMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Authorization\Model\Role|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleModelMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->getMock(\Magento\Backend\App\Action\Context::class, [], [], '', false);
        $this->coreRegistryMock = $this->getMock(\Magento\Framework\Registry::class, ['getId'], [], '', false);
        $this->roleFactoryMock = $this->getMock(\Magento\Authorization\Model\RoleFactory::class);
        $this->userFactoryMock = $this->getMock(\Magento\User\Model\UserFactory::class);
        $this->rulesFactoryMock = $this->getMock(\Magento\Authorization\Model\RulesFactory::class);
        $this->authSessionMock = $this->getMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['getUser'],
            [],
            '',
            false
        );
        $this->filterManagerMock = $this->getMock(\Magento\Framework\Filter\FilterManager::class, [], [], '', false);

        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'isPost']
        );
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $this->resultFactoryMock = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->messageManagerMock = $this->getMock(\Magento\Framework\Message\Manager::class, [], [], '', false);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->resultRedirectMock = $this->getMock(
            \Magento\Backend\Model\View\Result\Redirect::class,
            ['setPath'],
            [],
            '',
            false
        );
        $this->roleModelMock = $this->getMock(
            \Magento\Authorization\Model\Role::class,
            ['load', 'getId', 'getRoleType', 'delete'],
            [],
            '',
            false
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
     * Unit test with a non Post request.
     *
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Page not found
     * @return void
     */
    public function testExecuteWithNonPostMethod()
    {
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(false);

        $this->controller->execute();
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

        $this->createFactoryMock();
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
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

        $this->createFactoryMock();
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
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

        $this->createFactoryMock();
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
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

        $this->createFactoryMock();
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
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
     * Creates Classes needed in tests.
     *
     * @return void
     */
    private function createFactoryMock()
    {
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * Method which takes Id from request and check with User Role Id.
     *
     * @param int $id
     * @param int $userId
     * @param int $userRoleId
     * @return void
     */
    private function checkUserAndRoleIds($id, $userId, $userRoleId)
    {
        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->with('rid')->willReturn($id);

        $userModelMock = $this->getMock(
            \Magento\User\Model\User::class,
            ['getId', 'setId', 'getRoles'],
            [],
            '',
            false
        );
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
