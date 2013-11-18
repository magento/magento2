<?php
/**
 * \Magento\Webhook\Model\Webapi\EventHandler
 *
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Webapi;

class EventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webhook\Model\Webapi\EventHandler */
    protected $_eventHandler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_collection;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceAclUser;

    protected function setUp()
    {
        $this->_collection = $this->getMockBuilder('Magento\Webhook\Model\Resource\Subscription\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_resourceAclUser = $this->getMockBuilder('Magento\Webapi\Model\Resource\Acl\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_eventHandler = new \Magento\Webhook\Model\Webapi\EventHandler(
            $this->_collection,
            $this->_resourceAclUser
        );
    }

    public function testUserChanged()
    {
        $subscription = $this->_createMockSubscription();
        $this->_setMockSubscriptions($subscription);
        $user = $this->_createMockUser(1);

        $this->_eventHandler->userChanged($user);
    }

    public function testUserChangedNoSubscription()
    {
        $this->_setMockSubscriptions(array());
        $user = $this->_createMockUser(1);

        $this->_eventHandler->userChanged($user);
    }

    public function testRoleChanged()
    {
        $subscription = $this->_createMockSubscription();
        $this->_setMockSubscriptions($subscription);
        $roleId = 42;
        $role = $this->_createMockRole($roleId);
        $users = array($this->_createMockUser(2));
        $this->_setRoleUsersExpectation($users, $roleId);

        $this->_eventHandler->roleChanged($role);
    }

    public function testRoleChangedTwoUsers()
    {
        $subscription = $this->_createMockSubscription();
        $this->_setMockSubscriptions($subscription);
        $roleId = 42;
        $role = $this->_createMockRole($roleId);
        $users = array($this->_createMockUser(1), $this->_createMockUser(2));
        $this->_setRoleUsersExpectation($users, $roleId);

        $this->_eventHandler->roleChanged($role);
    }

    public function testRoleChangedTwoSubscriptions()
    {
        $subscriptions = array($this->_createMockSubscription(), $this->_createMockSubscription());
        $this->_setMockSubscriptions($subscriptions);
        $roleId = 42;
        $role = $this->_createMockRole($roleId);
        $users = array($this->_createMockUser(1));
        $this->_setRoleUsersExpectation($users, $roleId);

        $this->_eventHandler->roleChanged($role);
    }


    public function testTopicsNoLongerValid()
    {
        $subscription = $this->_createMockSubscription();
        $subscription->expects($this->once())
            ->method('findRestrictedTopics')
            ->will($this->returnValue(array('invalid/topic')));
        $subscription->expects($this->once())
            ->method('deactivate');
        $this->_setMockSubscriptions($subscription);
        $roleId = 1;
        $role = $this->_createMockRole($roleId);
        $users = array($this->_createMockUser(2));
        $this->_setRoleUsersExpectation($users, $roleId);

        $this->_eventHandler->roleChanged($role);
    }

    protected function _setRoleUsersExpectation($users, $roleId)
    {
        $this->_resourceAclUser->expects($this->atLeastOnce())
            ->method('getRoleUsers')
            ->with($roleId)
            ->will($this->returnValue($users));
    }

    protected function _createMockRole($roleId)
    {
        $role = $this->getMockBuilder('Magento\Webapi\Model\Acl\Role')
            ->disableOriginalConstructor()
            ->getMock();
        $role->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($roleId));
        return $role;
    }

    protected function _createMockUser($userId)
    {
        $user = $this->getMockBuilder('Magento\Webapi\Model\Acl\User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($userId));
        return $user;
    }

    protected function _createMockSubscription()
    {
        $subscription = $this->getMockBuilder('Magento\Webhook\Model\Subscription')
            ->disableOriginalConstructor()
            ->getMock();

        $subscription->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        return $subscription;
    }

    protected function _setMockSubscriptions($subscriptions)
    {
        if (!is_array($subscriptions)) {
            $subscriptions = array($subscriptions);
        }

        $this->_collection->expects($this->once())
            ->method('getApiUserSubscriptions')
            ->will($this->returnValue($subscriptions));
    }
}
