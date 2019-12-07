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
class AuthorizationServiceTest extends \PHPUnit\Framework\TestCase
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
        $this->roleMock = $this->createPartialMock(
            \Magento\Authorization\Model\Role::class,
            ['load', 'delete', '__wakeup', 'getId', 'save']
        );
        $this->roleMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->roleMock->expects($this->any())->method('delete')->will($this->returnSelf());
        $this->roleMock->expects($this->any())->method('save')->will($this->returnSelf());

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Authorization\Model\RoleFactory $roleFactoryMock */
        $roleFactoryMock = $this->createPartialMock(\Magento\Authorization\Model\RoleFactory::class, ['create']);
        $roleFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->roleMock));

        $roleCollectionFactoryMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory::class,
            ['create']
        );
        $roleCollectionMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Role\Collection::class,
            ['setUserFilter', 'getFirstItem']
        );
        $roleCollectionMock->expects($this->any())->method('setUserFilter')->will($this->returnSelf());
        $roleCollectionMock->expects($this->any())->method('getFirstItem')->will($this->returnValue($this->roleMock));

        $roleCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($roleCollectionMock));

        $rulesFactoryMock = $this->createPartialMock(\Magento\Authorization\Model\RulesFactory::class, ['create']);
        $this->rulesMock = $this->createPartialMock(
            \Magento\Authorization\Model\Rules::class,
            ['setRoleId', 'setResources', 'saveRel']
        );
        $rulesFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->rulesMock));

        $this->rootAclResourceMock = $this->createPartialMock(\Magento\Framework\Acl\RootResource::class, ['getId']);

        $this->integrationAuthorizationService = new AuthorizationService(
            $this->createMock(\Magento\Framework\Acl\Builder::class),
            $roleFactoryMock,
            $roleCollectionFactoryMock,
            $rulesFactoryMock,
            $this->createMock(\Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory::class),
            $this->createMock(\Psr\Log\LoggerInterface::class),
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
            ->will($this->throwException(new \Exception()));
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

        $result = $this->integrationAuthorizationService->grantPermissions(self::INTEGRATION_ID, $this->resources);
        $this->assertNull($result);
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

        $result = $this->integrationAuthorizationService->grantPermissions(self::INTEGRATION_ID, $this->resources);
        $this->assertNull($result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
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
        $this->rulesMock->expects($this->any())->method('saveRel')->will($this->throwException(new \Exception()));

        $this->integrationAuthorizationService->grantPermissions(self::INTEGRATION_ID, $this->resources);

        $this->expectExceptionMessage(
            'An error occurred during the attempt to grant permissions. For details, see the exceptions log.'
        );
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

        $result = $this->integrationAuthorizationService->grantAllPermissions(self::INTEGRATION_ID);
        $this->assertNull($result);
    }
}
