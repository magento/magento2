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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Webapi_EventHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $_eventHandler;
    protected $_subscriberFactory;
    protected $_resourceSubscriber;
    protected $_resourceAclUser;

    public function setUp()
    {
        $this->_subscriberFactory = $this->getMockBuilder('Mage_Webhook_Model_Subscriber_Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_resourceSubscriber = $this->getMockBuilder('Mage_Webhook_Model_Resource_Subscriber')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_resourceAclUser = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_eventHandler = new Mage_Webhook_Model_Webapi_EventHandler(
            $this->_subscriberFactory,
            $this->_resourceSubscriber,
            $this->_resourceAclUser
        );
    }

    public function testUserChanged()
    {
        $subscriber = $this->_createMockSubscriber();
        $this->_setMockSubscribers($subscriber);
        $user = $this->_createMockUser(1);

        $this->_eventHandler->userChanged($user);
    }

    public function testUserChanged_noSubscriber()
    {
        $user = $this->_createMockUser(1);

        $this->_eventHandler->userChanged($user);
    }

    public function testRoleChanged()
    {
        $subscriber = $this->_createMockSubscriber();
        $this->_setMockSubscribers($subscriber);
        $role = $this->_createMockRole(1);
        $users = array($this->_createMockUser(2));
        $this->_setRoleUsersExpectation($users);

        $this->_eventHandler->roleChanged($role);
    }

    public function testRoleChanged_twoUsers()
    {
        $subscriber = $this->_createMockSubscriber();
        $this->_setMockSubscribers($subscriber);
        $role = $this->_createMockRole(1);
        $users = array($this->_createMockUser(1), $this->_createMockUser(2));
        $this->_setRoleUsersExpectation($users);

        $this->_eventHandler->roleChanged($role);
    }

    public function testRoleChanged_twoSubscribers()
    {
        $subscribers = array($this->_createMockSubscriber(), $this->_createMockSubscriber());
        $this->_setMockSubscribers($subscribers);
        $role = $this->_createMockRole(1);
        $users = array($this->_createMockUser(1));
        $this->_setRoleUsersExpectation($users);

        $this->_eventHandler->roleChanged($role);
    }

    protected function _setRoleUsersExpectation($users)
    {
        $this->_resourceAclUser->expects($this->any())
            ->method('getRoleUsers')
            ->will($this->returnValue($users));
    }

    protected function _createMockRole($roleId)
    {
        $role = $this->getMockBuilder('Mage_Webapi_Model_Acl_Role')
            ->disableOriginalConstructor()
            ->getMock();
        $role->expects($this->any())
            ->method('getRoleId')
            ->will($this->returnValue($roleId));
        return $role;
    }

    protected function _createMockUser($userId)
    {
        $user = $this->getMockBuilder('Mage_Webapi_Model_Acl_User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->any())
            ->method('getUserId')
            ->will($this->returnValue($userId));
        return $user;
    }

    protected function _createMockSubscriber()
    {
        $subscriber = $this->getMockBuilder('Mage_Webhook_Model_Subscriber')
            ->disableOriginalConstructor()
            ->getMock();
        // Validate should be called at least once
        $subscriber->expects($this->once())
            ->method('validate');

        $subscriber->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        return $subscriber;
    }

    protected function _setMockSubscribers($subscribers) 
    {
        if (!is_array($subscribers)) {
            $subscribers = array($subscribers);
        }
        $subs = array();
        foreach ($subscribers as $subscriber) {
            $i = count($subs);
            $this->_subscriberFactory->expects($this->at($i))
                ->method('create')
                ->will($this->returnValue($subscriber));
            $subs[] = $i;
        }

        $this->_resourceSubscriber->expects($this->any())
            ->method('getApiUserSubscribers')
            ->will($this->returnValue($subs));
    }
}
