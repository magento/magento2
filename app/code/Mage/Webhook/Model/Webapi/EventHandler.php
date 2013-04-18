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

/**
 * Webapi EventHandler that should be notified if any relevant webapi events are received.
 * The event handler will decide what actions must be taken based on the events.
 */
class Mage_Webhook_Model_Webapi_EventHandler
{
    private $_modelSubscriberFactory;
    private $_resourceAclUser;
    private $_resourceSubscriber;


    public function __construct(
        Mage_Webhook_Model_Subscriber_Factory $modelSubscriberFactory,
        Mage_Webhook_Model_Resource_Subscriber $resourceSubscriber,
        Mage_Webapi_Model_Resource_Acl_User $resourceAclUser)
    {
        $this->_modelSubscriberFactory = $modelSubscriberFactory;
        $this->_resourceSubscriber = $resourceSubscriber;
        $this->_resourceAclUser = $resourceAclUser;
    }

    /**
     * Notifies the event handler that a webapi user has changed
     * @param  Mage_Webapi_Model_User $user User object that changed
     */
    public function userChanged($user)
    {
        // call helper that finds and notifies subscriber (user_id)
        $this->_validateSubscriptionsForUsers(array($user->getUserId()));
    }

    /**
     * Notifies the event handler that a webapi role has changed
     * @param  Mage_Webapi_Model_Role $role Role object that changed
     */
    public function roleChanged($role)
    {
        // get all users that contain this role (role_id)
        $users = $this->_getUserIdsFromRole($role->getRoleId());
        
        // for each user, call helper that finds and notifies subscriber (user_id)
        $this->_validateSubscriptionsForUsers($users);
    }

    /**
     * Queries Webapi for all the user ids that are currently using this role
     * @param  int $roleId 
     * @return array of ints representing the user ids.
     */
    private function _getUserIdsFromRole($roleId)
    {
        return $this->_getAclUserResource()->getRoleUsers($roleId);
    }

    /**
     * Finds all Subscriptions for the given users, and validates that these subscriptions
     * are still valid.
     * @param  array  $userIds users to check against
     */
    protected function _validateSubscriptionsForUsers(array $userIds)
    {
        $subscribers = array();

        foreach ($userIds as $userId) {
            $subsetIds = $this->_findSubscriberIdsForUser($userId);
            $subscribers = $this->_fetchSubscribers($subsetIds, $subscribers);
        }

        foreach ($subscribers as $subscriber) {
            $subscriber->validate();
        }
    }

    /**
     * Fetches subscriber models given an array of subscriber ids.  Will merge these
     * subscribers into an associative array where the key is the subscriber id.
     * This is done to avoid duplicate models.
     * @param  array $subscriberIds   An array of subscriber ids
     * @param  array  $subscribers An associative array of subscriber ids to subscriber models
     * @return array              An associate array of subscriber ids to subscriber models 
     */
    protected function _fetchSubscribers($subscriberIds, $subscribers = array())
    {
        if (!is_array($subscriberIds)) {
            return $subscribers;
        }

        foreach ($subscriberIds as $subId) {
            // Filter out duplicates, but load new unique models
            if (!isset($subscribers[$subId])) {
                $subscribers[$subId] = $this->_createSubscriber()->load($subId);
            }
        }

        return $subscribers;
    }


    /**
     * Given a userId, will find all Subscriber ids that are linked to this user 
     * @param  int $userId webapi user id
     * @return array         Array of subscriber ids
     */
    private function _findSubscriberIdsForUser($userId)
    {
        return $this->_getSubscriberResource()->getApiUserSubscribers($userId);
    }

    final protected function _createSubscriber()
    {
        return $this->_modelSubscriberFactory->create();
    }

    final protected function _getSubscriberResource()
    {
        return $this->_resourceSubscriber;
    }

    final protected function _getAclUserResource()
    {
        return $this->_resourceAclUser;
    }


}
