<?php
/**
 * Observer that handles webapi permission changes and bridges Magento events to webhook events
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Observer
{
    /** @var Mage_Webhook_Model_Webapi_EventHandler $_webapiEventHandler */
    private $_webapiEventHandler;

    /** @var  Mage_Webhook_Model_Resource_Subscription_Collection $_subscriptionSet */
    private $_subscriptionSet;

    /** @var Mage_Core_Model_Logger */
    private $_logger;

    /**
     * @param Mage_Webhook_Model_Webapi_EventHandler                        $webapiEventHandler
     * @param Mage_Webhook_Model_Resource_Subscription_Collection           $subscriptionSet
     * @param Mage_Core_Model_Logger                                        $logger
     */
    public function __construct(
        Mage_Webhook_Model_Webapi_EventHandler $webapiEventHandler,
        Mage_Webhook_Model_Resource_Subscription_Collection $subscriptionSet,
        Mage_Core_Model_Logger $logger
    ) {
        $this->_webapiEventHandler = $webapiEventHandler;
        $this->_subscriptionSet = $subscriptionSet;
        $this->_logger = $logger;
    }

    /**
     * Triggered after webapi user deleted. It updates status of the activated subscriptions
     * associated with this webapi user to inactive
     *
     * @return Mage_Webhook_Model_Observer
     */
    public function afterWebapiUserDelete()
    {
        try {
            $subscriptions = $this->_subscriptionSet->getActivatedSubscriptionsWithoutApiUser();
            if (count($subscriptions)) {
                $this->_resetActivation($subscriptions);
            }
        } catch (Exception $exception) {
            $this->_logger->logException($exception);
        }
        return $this;
    }

    /**
     * Triggered after webapi user change
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterWebapiUserChange(Varien_Event_Observer $observer)
    {
        try {
            $model = $observer->getEvent()->getObject();

            $this->_webapiEventHandler->userChanged($model);
        } catch (Exception $exception) {
            $this->_logger->logException($exception);
        }
    }

    /**
     * Triggered after webapi role change
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Webhook_Model_Observer
     */
    public function afterWebapiRoleChange(Varien_Event_Observer $observer)
    {
        try {
            $model = $observer->getEvent()->getObject();

            $this->_webapiEventHandler->roleChanged($model);
        } catch (Exception $exception) {
            $this->_logger->logException($exception);
        }
    }

    /**
     * Reset the subscriptions to the INACTIVE status.
     *
     * @param $subscriptions
     */
    protected function _resetActivation($subscriptions)
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $subscription->setStatus(Mage_Webhook_Model_Subscription::STATUS_INACTIVE)
                ->save();
        }
    }
}
