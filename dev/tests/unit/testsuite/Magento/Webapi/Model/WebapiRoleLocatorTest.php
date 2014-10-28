<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Model;

use Magento\Authorization\Model\Resource\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Resource\Role\Collection as RoleCollection;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;

class WebapiRoleLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\WebapiRoleLocator
     */
    protected $locator;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
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
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

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
            'Magento\Authorization\Model\Resource\Role\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->roleCollection = $this->getMockBuilder('Magento\Authorization\Model\Resource\Role\Collection')
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
