<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

use Magento\Authorization\Model\Resource\Rules;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Integration\Model\AuthorizationService;

class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sample role Id
     */
    const ROLE_ID = 1;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Role */
    protected $roleMock;

    /** @var AuthorizationService */
    protected $integrationAuthorizationService;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Rules */
    protected $rulesMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|RootResource */
    protected $rootAclResourceMock;

    /**
     * @var array
     */
    protected $resources;

    protected function setUp()
    {
        $this->roleMock = $this->getMock(
            'Magento\Authorization\Model\Role',
            ['load', 'delete', '__wakeup', 'getId'],
            [],
            '',
            false
        );
        $this->roleMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->roleMock->expects($this->any())->method('delete')->will($this->returnSelf());
        $this->roleMock->expects($this->any())->method('getId')->will($this->returnValue(self::ROLE_ID));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Authorization\Model\RoleFactory $roleFactoryMock */
        $roleFactoryMock = $this->getMock(
            'Magento\Authorization\Model\RoleFactory',
            ['create'],
            [],
            '',
            false
        );
        $roleFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->roleMock));

        $roleCollectionFactoryMock = $this->getMock(
            'Magento\Authorization\Model\Resource\Role\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $roleCollectionMock = $this->getMock(
            'Magento\Authorization\Model\Resource\Role\Collection',
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

        $rulesFactoryMock = $this->getMock('Magento\Authorization\Model\RulesFactory', ['create'], [], '', false);
        $this->rulesMock = $this->getMock(
            'Magento\Authorization\Model\Rules',
            ['setRoleId', 'setResources', 'saveRel'],
            [],
            '',
            false
        );
        $rulesFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->rulesMock));

        $this->rootAclResourceMock = $this->getMock('Magento\Framework\Acl\RootResource', ['getId'], [], '', false);

        $this->integrationAuthorizationService = new AuthorizationService(
            $this->getMock('Magento\Framework\Acl\Builder', [], [], '', false),
            $roleFactoryMock,
            $roleCollectionFactoryMock,
            $rulesFactoryMock,
            $this->getMock('Magento\Authorization\Model\Resource\Rules\CollectionFactory', [], [], '', false),
            $this->getMock('Psr\Log\LoggerInterface'),
            $this->rootAclResourceMock
        );
    }

    public function testRemovePermissions()
    {
        $integrationId = 22;
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . $integrationId;
        $this->roleMock->expects($this->once())->method('load')->with($roleName)->will($this->returnSelf());
        $this->integrationAuthorizationService->removePermissions($integrationId);
    }

    public function testGrantPermissions()
    {
        $integrationId = 22;
        $this->resources = [
            'Magento_Sales::sales',
            'Magento_Sales::sales_operations',
            'Magento_Cart::cart',
            'Magento_Cart::manage'
        ];

        $this->rulesMock->expects($this->any())->method('setRoleId')->with(self::ROLE_ID)->will($this->returnSelf());
        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with($this->resources)
            ->will($this->returnSelf());
        $this->rulesMock->expects($this->any())->method('saveRel')->will($this->returnSelf());

        $this->integrationAuthorizationService->grantPermissions($integrationId, $this->resources);
    }

    public function testGrantAllPermissions()
    {
        $integrationId = 22;
        $rootResource = 'Magento_All:all';

        $this->rootAclResourceMock->expects($this->any())->method('getId')->will($this->returnValue($rootResource));
        $this->rulesMock->expects($this->any())->method('setRoleId')->with(self::ROLE_ID)->will($this->returnSelf());
        $this->rulesMock->expects($this->any())
            ->method('setResources')
            ->with([$rootResource])
            ->will($this->returnSelf());
        $this->rulesMock->expects($this->any())->method('saveRel')->will($this->returnSelf());

        $this->integrationAuthorizationService->grantAllPermissions($integrationId);
    }
}
