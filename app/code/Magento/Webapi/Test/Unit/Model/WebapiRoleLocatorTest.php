<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Authorization\Model\CompositeUserContext;
use Magento\Authorization\Model\ResourceModel\Role\Collection as RoleCollection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Model\WebapiRoleLocator;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WebapiRoleLocatorTest extends TestCase
{
    /**
     * @var WebapiRoleLocator
     */
    protected $locator;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var RoleCollectionFactory
     */
    protected $roleCollectionFactory;

    /**
     * @var RoleCollection
     */
    protected $roleCollection;

    /**
     * @var Role
     */
    protected $role;

    /**
     * @var ObjectManager
     */
    private $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $userId = 'userId';
        $userType = 'userType';

        $this->userContext = $this->getMockBuilder(CompositeUserContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getUserType'])
            ->getMock();
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);
        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->willReturn($userType);

        $this->roleCollectionFactory = $this->getMockBuilder(
            \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();

        $this->roleCollection = $this->getMockBuilder(\Magento\Authorization\Model\ResourceModel\Role\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUserFilter', 'getFirstItem'])
            ->getMock();
        $this->roleCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->roleCollection);
        $this->roleCollection->expects($this->once())
            ->method('setUserFilter')
            ->with($userId, $userType)
            ->willReturn($this->roleCollection);

        $this->role = $this->getMockBuilder(Role::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        $this->roleCollection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($this->role);

        $this->locator = $this->_objectManager->getObject(
            WebapiRoleLocator::class,
            [
                'userContext' => $this->userContext,
                'roleCollectionFactory' => $this->roleCollectionFactory
            ]
        );
    }

    public function testNoRoleId()
    {
        $this->role->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->assertEquals('', $this->locator->getAclRoleId());
    }

    public function testGetAclRoleId()
    {
        $roleId = 9;

        $this->role->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($roleId);

        $this->assertEquals($roleId, $this->locator->getAclRoleId());
    }
}
