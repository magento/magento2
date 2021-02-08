<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Test\Unit\Model\Acl;

use Magento\Authorization\Model\Acl\AclRetriever;

use Magento\Authorization\Model\ResourceModel\Role\Collection as RoleCollection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\ResourceModel\Rules\Collection as RulesCollection;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory as RulesCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AclRetrieverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AclRetriever
     */
    protected $aclRetriever;

    /** @var \PHPUnit\Framework\MockObject\MockObject|Role $roleMock */
    protected $roleMock;

    protected function setup(): void
    {
        $this->aclRetriever = $this->createAclRetriever();
    }

    public function testGetAllowedResourcesByUserTypeGuest()
    {
        $expectedResources = ['anonymous'];
        $allowedResources = $this->aclRetriever->getAllowedResourcesByUser(UserContextInterface::USER_TYPE_GUEST, null);
        $this->assertEquals(
            $expectedResources,
            $allowedResources,
            'Allowed resources for guests should be \'anonymous\'.'
        );
    }

    public function testGetAllowedResourcesByUserTypeCustomer()
    {
        $expectedResources = ['self'];
        $allowedResources = $this->aclRetriever->getAllowedResourcesByUser(
            UserContextInterface::USER_TYPE_CUSTOMER,
            null
        );
        $this->assertEquals(
            $expectedResources,
            $allowedResources,
            'Allowed resources for customers should be \'self\'.'
        );
    }

    /**
     */
    public function testGetAllowedResourcesByUserRoleNotFound()
    {
        $this->expectException(\Magento\Framework\Exception\AuthorizationException::class);
        $this->expectExceptionMessage('The role wasn\'t found for the user. Verify the role and try again.');

        $this->roleMock->expects($this->once())->method('getId')->willReturn(null);
        $this->aclRetriever->getAllowedResourcesByUser(UserContextInterface::USER_TYPE_INTEGRATION, null);
    }

    public function testGetAllowedResourcesByUser()
    {
        $this->roleMock->expects($this->any())->method('getId')->willReturn(1);
        $expectedResources = ['Magento_Backend::dashboard', 'Magento_Cms::page'];
        $this->assertEquals(
            $expectedResources,
            $this->aclRetriever->getAllowedResourcesByUser(UserContextInterface::USER_TYPE_INTEGRATION, 1)
        );
    }

    /**
     * @return AclRetriever
     */
    protected function createAclRetriever()
    {
        $this->roleMock = $this->createPartialMock(\Magento\Authorization\Model\Role::class, ['getId', '__wakeup']);

        /** @var \PHPUnit\Framework\MockObject\MockObject|RoleCollection $roleCollectionMock */
        $roleCollectionMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Role\Collection::class,
            ['setUserFilter', 'getFirstItem']
        );
        $roleCollectionMock->expects($this->any())->method('setUserFilter')->willReturnSelf();
        $roleCollectionMock->expects($this->any())->method('getFirstItem')->willReturn($this->roleMock);

        /** @var \PHPUnit\Framework\MockObject\MockObject|RoleCollectionFactory $roleCollectionFactoryMock */
        $roleCollectionFactoryMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory::class,
            ['create']
        );
        $roleCollectionFactoryMock->expects($this->any())->method('create')->willReturn(
            $roleCollectionMock
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Authorization\Model\Rules $rulesMock1 */
        $rulesMock1 = $this->createPartialMock(
            \Magento\Authorization\Model\Rules::class,
            ['getResourceId', '__wakeup']
        );
        $rulesMock1->expects($this->any())->method('getResourceId')->willReturn(
            'Magento_Backend::dashboard'
        );
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Authorization\Model\Rules $rulesMock1 */
        $rulesMock2 = $this->createPartialMock(
            \Magento\Authorization\Model\Rules::class,
            ['getResourceId', '__wakeup']
        );
        $rulesMock2->expects($this->any())->method('getResourceId')->willReturn('Magento_Cms::page');

        /** @var \PHPUnit\Framework\MockObject\MockObject|RulesCollection $rulesCollectionMock */
        $rulesCollectionMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Rules\Collection::class,
            ['getByRoles', 'load', 'getItems']
        );
        $rulesCollectionMock->expects($this->any())->method('getByRoles')->willReturnSelf();
        $rulesCollectionMock->expects($this->any())->method('load')->willReturnSelf();
        $rulesCollectionMock->expects($this->any())->method('getItems')->willReturn(
            [$rulesMock1, $rulesMock2]
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject|RulesCollectionFactory $rulesCollectionFactoryMock */
        $rulesCollectionFactoryMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory::class,
            ['create']
        );
        $rulesCollectionFactoryMock->expects($this->any())->method('create')->willReturn(
            $rulesCollectionMock
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Acl $aclMock */
        $aclMock = $this->createPartialMock(\Magento\Framework\Acl::class, ['has', 'isAllowed']);
        $aclMock->expects($this->any())->method('has')->willReturn(true);
        $aclMock->expects($this->any())->method('isAllowed')->willReturn(true);

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Acl\Builder $aclBuilderMock */
        $aclBuilderMock = $this->createPartialMock(\Magento\Framework\Acl\Builder::class, ['getAcl']);
        $aclBuilderMock->expects($this->any())->method('getAcl')->willReturn($aclMock);

        return new AclRetriever(
            $aclBuilderMock,
            $roleCollectionFactoryMock,
            $rulesCollectionFactoryMock,
            $this->createMock(\Psr\Log\LoggerInterface::class)
        );
    }
}
