<?php declare(strict_types=1);
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
use Magento\Authorization\Model\Rules;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AclRetrieverTest extends TestCase
{
    /**
     * @var AclRetriever
     */
    protected $aclRetriever;

    /** @var MockObject|Role $roleMock */
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

    public function testGetAllowedResourcesByUserRoleNotFound()
    {
        $this->expectException('Magento\Framework\Exception\AuthorizationException');
        $this->expectExceptionMessage('The role wasn\'t found for the user. Verify the role and try again.');
        $this->roleMock->expects($this->once())->method('getId')->will($this->returnValue(null));
        $this->aclRetriever->getAllowedResourcesByUser(UserContextInterface::USER_TYPE_INTEGRATION, null);
    }

    public function testGetAllowedResourcesByUser()
    {
        $this->roleMock->method('getId')->will($this->returnValue(1));
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
        $this->roleMock = $this->createPartialMock(Role::class, ['getId', '__wakeup']);

        /** @var MockObject|RoleCollection $roleCollectionMock */
        $roleCollectionMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Role\Collection::class,
            ['setUserFilter', 'getFirstItem']
        );
        $roleCollectionMock->method('setUserFilter')->will($this->returnSelf());
        $roleCollectionMock->method('getFirstItem')->will($this->returnValue($this->roleMock));

        /** @var MockObject|RoleCollectionFactory $roleCollectionFactoryMock */
        $roleCollectionFactoryMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory::class,
            ['create']
        );
        $roleCollectionFactoryMock->method('create')->will(
            $this->returnValue($roleCollectionMock)
        );

        /** @var MockObject|Rules $rulesMock1 */
        $rulesMock1 = $this->createPartialMock(
            Rules::class,
            ['getResourceId', '__wakeup']
        );
        $rulesMock1->method('getResourceId')->will(
            $this->returnValue('Magento_Backend::dashboard')
        );
        /** @var MockObject|Rules $rulesMock1 */
        $rulesMock2 = $this->createPartialMock(
            Rules::class,
            ['getResourceId', '__wakeup']
        );
        $rulesMock2->method('getResourceId')->will($this->returnValue('Magento_Cms::page'));

        /** @var MockObject|RulesCollection $rulesCollectionMock */
        $rulesCollectionMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Rules\Collection::class,
            ['getByRoles', 'load', 'getItems']
        );
        $rulesCollectionMock->method('getByRoles')->will($this->returnSelf());
        $rulesCollectionMock->method('load')->will($this->returnSelf());
        $rulesCollectionMock->method('getItems')->will(
            $this->returnValue([$rulesMock1, $rulesMock2])
        );

        /** @var MockObject|RulesCollectionFactory $rulesCollectionFactoryMock */
        $rulesCollectionFactoryMock = $this->createPartialMock(
            \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory::class,
            ['create']
        );
        $rulesCollectionFactoryMock->method('create')->will(
            $this->returnValue($rulesCollectionMock)
        );

        /** @var MockObject|Acl $aclMock */
        $aclMock = $this->createPartialMock(Acl::class, ['has', 'isAllowed']);
        $aclMock->method('has')->will($this->returnValue(true));
        $aclMock->method('isAllowed')->will($this->returnValue(true));

        /** @var MockObject|Builder $aclBuilderMock */
        $aclBuilderMock = $this->createPartialMock(Builder::class, ['getAcl']);
        $aclBuilderMock->method('getAcl')->will($this->returnValue($aclMock));

        return new AclRetriever(
            $aclBuilderMock,
            $roleCollectionFactoryMock,
            $rulesCollectionFactoryMock,
            $this->createMock(LoggerInterface::class)
        );
    }
}
