<?php
/**
 * Webapi EventHandler that should be notified if any relevant webapi events are received.
 *
 * The event handler will decide what actions must be taken based on the events.
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

class EventHandler
{
    /** @var \Magento\Webapi\Model\Resource\Acl\User  */
    private $_resourceAclUser;

    /** @var \Magento\Webhook\Model\Resource\Subscription\Collection  */
    private $_subscriptionSet;

    /**
     * @param \Magento\Webhook\Model\Resource\Subscription\Collection $subscriptionSet
     * @param \Magento\Webapi\Model\Resource\Acl\User $resourceAclUser
     */
    public function __construct(
        \Magento\Webhook\Model\Resource\Subscription\Collection $subscriptionSet,
        \Magento\Webapi\Model\Resource\Acl\User $resourceAclUser
    ) {
        $this->_subscriptionSet = $subscriptionSet;
        $this->_resourceAclUser = $resourceAclUser;
    }

    /**
     * Notifies the event handler that a webapi user has changed
     *
     * @param  \Magento\Webapi\Model\Acl\User $user User object that changed
     */
    public function userChanged($user)
    {
        // call helper that finds and notifies subscription (user_id)
        $this->_validateSubscriptionsForUsers(array($user->getId()));
    }

    /**
     * Notifies the event handler that a webapi role has changed
     *
     * @param  \Magento\Webapi\Model\Acl\Role $role Role object that changed
     */
    public function roleChanged($role)
    {
        // get all users that contain this role (role_id)
        $users = $this->_resourceAclUser->getRoleUsers($role->getId());
        
        // for each user, call helper that finds and notifies subscription (user_id)
        $this->_validateSubscriptionsForUsers($users);
    }

    /**
     * Finds all Subscriptions for the given users, and validates that these subscriptions are still valid.
     *
     * @param  array  $userIds users to check against
     */
    protected function _validateSubscriptionsForUsers(array $userIds)
    {
        $subscriptions = $this->_subscriptionSet->getApiUserSubscriptions($userIds);

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            if ($subscription->findRestrictedTopics()) {
                $subscription->deactivate();
                $subscription->save();
            }
        }
    }
}
