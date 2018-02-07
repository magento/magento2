<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Authorization\Model\ResourceModel\Role\Collection as RoleCollection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;

class WebapiRoleLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\WebapiRoleLocator
     */
    protected $locator;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
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

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $userId = 'userId';
        $userType = 'userType';

        $this->userContext = $this->getMockBuilder('Magento\Authorization\Model\CompositeUserContext')
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getUserType'])
            ->getMock();
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));
        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->will($this->returnValue($userType));

        $this->roleCollectionFactory = $this->getMockBuilder(
            'Magento\Authorization\Model\ResourceModel\Role\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->roleCollection = $this->getMockBuilder('Magento\Authorization\Model\ResourceModel\Role\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['setUserFilter', 'getFirstItem'])
            ->getMock();
        $this->roleCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->roleCollection));
        $this->roleCollection->expects($this->once())
            ->method('setUserFilter')
            ->with($userId, $userType)
            ->will($this->returnValue($this->roleCollection));

        $this->role = $this->getMockBuilder('Magento\Authorization\Model\Role')
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        $this->roleCollection->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($this->role));

        $this->locator = $this->_objectManager->getObject(
            'Magento\Webapi\Model\WebapiRoleLocator',
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
            ->will($this->returnValue(null));

        $this->assertEquals('', $this->locator->getAclRoleId());
    }

    public function testGetAclRoleId()
    {
        $roleId = 9;

        $this->role->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue($roleId));

        $this->assertEquals($roleId, $this->locator->getAclRoleId());
    }
}
