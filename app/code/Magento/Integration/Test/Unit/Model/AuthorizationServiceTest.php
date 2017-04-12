<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

use Magento\Authorization\Model\ResourceModel\Rules;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Integration\Model\AuthorizationService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sample role Id
     */
    const ROLE_ID = 1;

    /**
     * Sample integration id
     */
    const INTEGRATION_ID = 22;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Role
     */
    protected $roleMock;

    /**
     * @var AuthorizationService
     */
    protected $integrationAuthorizationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Rules
     */
    protected $rulesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RootResource
     */
    protected $rootAclResourceMock;

    /**
     * @var array
     */
    protected $resources;

    protected function setUp()
    {
        $this->roleMock = $this->getMock(
            \Magento\Authorization\Model\Role::class,
            ['load', 'delete', '__wakeup', 'getId', 'save'],
            [],
            '',
            false
        );
        $this->roleMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->roleMock->expects($this->any())->method('delete')->will($this->returnSelf());
        $this->roleMock->expects($this->any())->method('save')->will($this->returnSelf());

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Authorization\Model\RoleFactory $roleFactoryMock */
        $roleFactoryMock = $this->getMock(
            \Magento\Authorization\Model\RoleFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $roleFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->roleMock));

        $roleCollectionFactoryMock = $this->getMock(
            \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $roleCollectionMock = $this->getMock(
            \Magento\Authorization\Model\ResourceModel\Role\Collection::class,
            ['setUserFilter', 'getFirstItem'],
            [],
            '',
            false
        );
        $roleCollectionMock->expects($this->any())->method('setUserFilter')->will($this->returnSelf());
        $roleCollectionMock->expects($this->any())->method('getFirstItem')->will($this->returnValue($this->roleMock));

        $roleCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($roleCollectionMock));

        $rulesFactoryMock = $this->getMock(\Magento\Authorization\Model\RulesFactory::class, ['create'], [], '', false);
        $this->rulesMock = $this->getMock(
            \Magento\Authorization\Model\Rules::class,
            ['setRoleId', 'setResources', 'saveRel'],
            [],
            '',
            false
        );
        $rulesFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->rulesMock));

        $this->rootAclResourceMock = $this->getMock(
            \Magento\Framework\Acl\RootResource::class,
            ['getId'],
            [],
            '',
            false
        );

        $this->integrationAuthorizationService = new AuthorizationService(
            $this->getMock(\Magento\Framework\Acl\Builder::class, [], [], '', false),
            $roleFactoryMock,
            $roleCollectionFactoryMock,
            $rulesFactoryMock,
            $this->getMock(
                \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory::class,
                [],
                [],
                '',
                false
            ),
            $this->getMock(\Psr\Log\LoggerInterface::class),
            $this->rootAclResourceMock
        );
    }

    public function testRemovePermissions()
    {
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . self::INTEGRATION_ID;
        $this->roleMock->expects($this->once())->method('load')->with($roleName)->will($this->returnSelf());
        $this->integrationAuthorizationService->removePermissions(self::INTEGRATION_ID);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong while deleting roles and permissions.
     */
    public function testRemovePermissionsException()
    {
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . self::INTEGRATION_ID;
        $this->roleMock->expects($this->once())
            ->method('load')
            ->with($roleName)
            ->will($this->throwException(new \Exception));
        $this->integrationAuthorizationService->removePermissions(self::INTEGRATION_ID);
    }

    public function testGrantPermissions()
    {
        $this->resources = [
            'Magento_Sales::sales',
            'Magento_Sales::sales_operations',
            'Magento_Cart::cart',
            'Magento_Cart::manage'
        ];

        $this->roleMock->expects($this->any())->method('getId')->will($this->returnValue(self::ROLE_ID));
        $this->rulesMock->expects($this->any())->method('setRoleId')->with(self::ROLE_ID)->will($this->returnSelf());
        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with($this->resources)
            ->will($this->returnSelf());
        $this->rulesMock->expects($this->any())->method('saveRel')->will($this->returnSelf());

        $this->integrationAuthorizationService->grantPermissions(self::INTEGRATION_ID, $this->resources);
    }

    public function testGrantPermissionsNoRole()
    {
        $calculatedRoleId = UserContextInterface::USER_TYPE_INTEGRATION . self::INTEGRATION_ID;

        $this->resources = [
            'Magento_Sales::sales',
            'Magento_Sales::sales_operations',
            'Magento_Cart::cart',
            'Magento_Cart::manage'
        ];

        //Return invalid role
        $this->roleMock->expects($this->any())
            ->method('getId')
            ->will($this->onConsecutiveCalls(null, $calculatedRoleId));
        // Verify if the method is called with the newly created role
        $this->rulesMock->expects($this->any())
            ->method('setRoleId')
            ->with($calculatedRoleId)
            ->will($this->returnSelf());

        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with($this->resources)
            ->will($this->returnSelf());
        $this->rulesMock->expects($this->any())->method('saveRel')->will($this->returnSelf());

        $this->integrationAuthorizationService->grantPermissions(self::INTEGRATION_ID, $this->resources);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Sorry, something went wrong granting permissions.
     */
    public function testGrantPermissionsException()
    {
        $this->resources = [
            'Magento_Sales::sales',
            'Magento_Sales::sales_operations',
            'Magento_Cart::cart',
            'Magento_Cart::manage'
        ];

        $this->roleMock->expects($this->any())->method('getId')->will($this->returnValue(self::ROLE_ID));
        $this->rulesMock->expects($this->any())->method('setRoleId')->with(self::ROLE_ID)->will($this->returnSelf());
        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with($this->resources)
            ->will($this->returnSelf());
        $this->rulesMock->expects($this->any())->method('saveRel')->will($this->throwException(new \Exception));

        $this->integrationAuthorizationService->grantPermissions(self::INTEGRATION_ID, $this->resources);
    }

    public function testGrantAllPermissions()
    {
        $rootResource = 'Magento_All:all';

        $this->rootAclResourceMock->expects($this->any())->method('getId')->will($this->returnValue($rootResource));
        $this->roleMock->expects($this->any())->method('getId')->will($this->returnValue(self::ROLE_ID));
        $this->rulesMock->expects($this->any())->method('setRoleId')->with(self::ROLE_ID)->will($this->returnSelf());
        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with([$rootResource])
            ->will($this->returnSelf());
        $this->rulesMock->expects($this->any())->method('saveRel')->will($this->returnSelf());

        $this->integrationAuthorizationService->grantAllPermissions(self::INTEGRATION_ID);
    }
}
