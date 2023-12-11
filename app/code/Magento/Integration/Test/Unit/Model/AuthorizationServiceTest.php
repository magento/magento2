<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory;
use Magento\Authorization\Model\ResourceModel\Rules;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Rules as AuthorizationRules;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Acl\RootResource;
use Magento\Integration\Model\AuthorizationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthorizationServiceTest extends TestCase
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
     * @var MockObject|Role
     */
    protected $roleMock;

    /**
     * @var AuthorizationService
     */
    protected $integrationAuthorizationService;

    /**
     * @var MockObject|Rules
     */
    protected $rulesMock;

    /**
     * @var MockObject|RootResource
     */
    protected $rootAclResourceMock;

    /**
     * @var array
     */
    protected $resources;

    protected function setUp(): void
    {
        $this->roleMock = $this->createPartialMock(
            Role::class,
            ['load', 'delete', '__wakeup', 'getId', 'save']
        );
        $this->roleMock->expects($this->any())->method('load')->willReturnSelf();
        $this->roleMock->expects($this->any())->method('delete')->willReturnSelf();
        $this->roleMock->expects($this->any())->method('save')->willReturnSelf();

        /** @var MockObject|RoleFactory $roleFactoryMock */
        $roleFactoryMock = $this->createPartialMock(RoleFactory::class, ['create']);
        $roleFactoryMock->expects($this->any())->method('create')->willReturn($this->roleMock);

        $roleCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $roleCollectionMock = $this->createPartialMock(
            Collection::class,
            ['setUserFilter', 'getFirstItem']
        );
        $roleCollectionMock->expects($this->any())->method('setUserFilter')->willReturnSelf();
        $roleCollectionMock->expects($this->any())->method('getFirstItem')->willReturn($this->roleMock);

        $roleCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($roleCollectionMock);

        $rulesFactoryMock = $this->createPartialMock(RulesFactory::class, ['create']);
        $this->rulesMock = $this->getMockBuilder(AuthorizationRules::class)
            ->addMethods(['setRoleId', 'setResources'])
            ->onlyMethods(['saveRel'])
            ->disableOriginalConstructor()
            ->getMock();
        $rulesFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->rulesMock);

        $this->rootAclResourceMock = $this->createPartialMock(RootResource::class, ['getId']);

        $this->integrationAuthorizationService = new AuthorizationService(
            $this->createMock(Builder::class),
            $roleFactoryMock,
            $roleCollectionFactoryMock,
            $rulesFactoryMock,
            $this->createMock(\Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory::class),
            $this->getMockForAbstractClass(LoggerInterface::class),
            $this->rootAclResourceMock
        );
    }

    public function testRemovePermissions()
    {
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . self::INTEGRATION_ID;
        $this->roleMock->expects($this->once())->method('load')->with($roleName)->willReturnSelf();
        $this->integrationAuthorizationService->removePermissions(self::INTEGRATION_ID);
    }

    public function testRemovePermissionsException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Something went wrong while deleting roles and permissions.');
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . self::INTEGRATION_ID;
        $this->roleMock->expects($this->once())
            ->method('load')
            ->with($roleName)
            ->willThrowException(new \Exception());
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

        $this->roleMock->expects($this->any())->method('getId')->willReturn(self::ROLE_ID);
        $this->rulesMock->expects($this->any())->method('setRoleId')->with(self::ROLE_ID)->willReturnSelf();
        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with($this->resources)->willReturnSelf();
        $this->rulesMock->expects($this->any())->method('saveRel')->willReturnSelf();

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
            ->with($calculatedRoleId)->willReturnSelf();

        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with($this->resources)->willReturnSelf();
        $this->rulesMock->expects($this->any())->method('saveRel')->willReturnSelf();

        $result = $this->integrationAuthorizationService->grantPermissions(self::INTEGRATION_ID, $this->resources);
        $this->assertNull($result);
    }

    public function testGrantPermissionsException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->resources = [
            'Magento_Sales::sales',
            'Magento_Sales::sales_operations',
            'Magento_Cart::cart',
            'Magento_Cart::manage'
        ];

        $this->roleMock->expects($this->any())->method('getId')->willReturn(self::ROLE_ID);
        $this->rulesMock->expects($this->any())->method('setRoleId')->with(self::ROLE_ID)->willReturnSelf();
        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with($this->resources)->willReturnSelf();
        $this->rulesMock->expects($this->any())->method('saveRel')->willThrowException(new \Exception());

        $this->integrationAuthorizationService->grantPermissions(self::INTEGRATION_ID, $this->resources);

        $this->expectExceptionMessage(
            'An error occurred during the attempt to grant permissions. For details, see the exceptions log.'
        );
    }

    public function testGrantAllPermissions()
    {
        $rootResource = 'Magento_All:all';

        $this->rootAclResourceMock->expects($this->any())->method('getId')->willReturn($rootResource);
        $this->roleMock->expects($this->any())->method('getId')->willReturn(self::ROLE_ID);
        $this->rulesMock->expects($this->any())->method('setRoleId')->with(self::ROLE_ID)->willReturnSelf();
        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with([$rootResource])->willReturnSelf();
        $this->rulesMock->expects($this->any())->method('saveRel')->willReturnSelf();

        $result = $this->integrationAuthorizationService->grantAllPermissions(self::INTEGRATION_ID);
        $this->assertNull($result);
    }
}
