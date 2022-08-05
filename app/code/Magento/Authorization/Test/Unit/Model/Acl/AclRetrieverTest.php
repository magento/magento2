<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
use Magento\Framework\Exception\AuthorizationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Magento\Authorization\Model\Acl\AclRetriever
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AclRetrieverTest extends TestCase
{
    /**
     * @var AclRetriever
     */
    private $aclRetriever;

    /**
     * @var Role|MockObject
     */
    private $roleMock;

    protected function setUp(): void
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
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage("The role wasn't found for the user. Verify the role and try again.");

        $this->roleMock->expects($this->once())->method('getId')->willReturn(null);
        $this->aclRetriever->getAllowedResourcesByUser(UserContextInterface::USER_TYPE_INTEGRATION, null);
    }

    public function testGetAllowedResourcesByUser()
    {
        $this->roleMock->method('getId')->willReturn(1);
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

        /**
         * @var RoleCollection|MockObject $roleCollectionMock
         */
        $roleCollectionMock = $this->createPartialMock(
            RoleCollection::class,
            ['setUserFilter', 'getFirstItem']
        );
        $roleCollectionMock->method('setUserFilter')->willReturnSelf();
        $roleCollectionMock->method('getFirstItem')->willReturn($this->roleMock);

        /**
         * @var RoleCollectionFactory|MockObject $roleCollectionFactoryMock
         */
        $roleCollectionFactoryMock = $this->createPartialMock(
            RoleCollectionFactory::class,
            ['create']
        );
        $roleCollectionFactoryMock->method('create')->willReturn(
            $roleCollectionMock
        );

        /**
         * @var Rules|MockObject $rulesMock1
         */
        $rulesMock1 = $this->getMockBuilder(Rules::class)
            ->addMethods(['getResourceId'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $rulesMock1->method('getResourceId')->willReturn(
            'Magento_Backend::dashboard'
        );
        /**
         * @var Rules|MockObject $rulesMock2
         */
        $rulesMock2 = $this->getMockBuilder(Rules::class)
            ->addMethods(['getResourceId'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $rulesMock2->method('getResourceId')->willReturn('Magento_Cms::page');

        /**
         * @var RulesCollection|MockObject $rulesCollectionMock
         */
        $rulesCollectionMock = $this->createPartialMock(
            RulesCollection::class,
            ['getByRoles', 'load', 'getItems']
        );
        $rulesCollectionMock->method('getByRoles')->willReturnSelf();
        $rulesCollectionMock->method('load')->willReturnSelf();
        $rulesCollectionMock->method('getItems')->willReturn(
            [$rulesMock1, $rulesMock2]
        );

        /**
         * @var RulesCollectionFactory|MockObject $rulesCollectionFactoryMock
         */
        $rulesCollectionFactoryMock = $this->createPartialMock(
            RulesCollectionFactory::class,
            ['create']
        );
        $rulesCollectionFactoryMock->expects($this->any())->method('create')->willReturn(
            $rulesCollectionMock
        );

        /**
         * @var Acl|MockObject $aclMock
         */
        $aclMock = $this->createPartialMock(Acl::class, ['has', 'isAllowed']);
        $aclMock->expects($this->any())->method('has')->willReturn(true);
        $aclMock->expects($this->any())->method('isAllowed')->willReturn(true);

        /**
         * @var Builder|MockObject $aclBuilderMock
         */
        $aclBuilderMock = $this->createPartialMock(Builder::class, ['getAcl']);
        $aclBuilderMock->expects($this->any())->method('getAcl')->willReturn($aclMock);

        return new AclRetriever(
            $aclBuilderMock,
            $roleCollectionFactoryMock,
            $rulesCollectionFactoryMock,
            $this->getMockForAbstractClass(LoggerInterface::class)
        );
    }
}
