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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model;

class Observer
{
    /** @var \Magento\Webhook\Model\Webapi\EventHandler $_webapiEventHandler */
    private $_webapiEventHandler;

    /** @var  \Magento\Webhook\Model\Resource\Subscription\Collection $_subscriptionSet */
    private $_subscriptionSet;

    /** @var \Magento\Core\Model\Logger */
    private $_logger;

    /**
     * @param \Magento\Webhook\Model\Webapi\EventHandler                        $webapiEventHandler
     * @param \Magento\Webhook\Model\Resource\Subscription\Collection           $subscriptionSet
     * @param \Magento\Core\Model\Logger                                        $logger
     */
    public function __construct(
        \Magento\Webhook\Model\Webapi\EventHandler $webapiEventHandler,
        \Magento\Webhook\Model\Resource\Subscription\Collection $subscriptionSet,
        \Magento\Core\Model\Logger $logger
    ) {
        $this->_webapiEventHandler = $webapiEventHandler;
        $this->_subscriptionSet = $subscriptionSet;
        $this->_logger = $logger;
    }

    /**
     * Triggered after webapi user deleted. It updates status of the activated subscriptions
     * associated with this webapi user to inactive
     */
    public function afterWebapiUserDelete()
    {
        try {
            $subscriptions = $this->_subscriptionSet->getActivatedSubscriptionsWithoutApiUser();
            /** @var \Magento\Webhook\Model\Subscription $subscription */
            foreach ($subscriptions as $subscription) {
                $subscription->setStatus(\Magento\Webhook\Model\Subscription::STATUS_INACTIVE)
                    ->save();
            }
        } catch (\Exception $exception) {
            $this->_logger->logException($exception);
        }
    }

    /**
     * Triggered after webapi user change
     *
     * @param \Magento\Event\Observer $observer
     */
    public function afterWebapiUserChange(\Magento\Event\Observer $observer)
    {
        try {
            $model = $observer->getEvent()->getObject();

            $this->_webapiEventHandler->userChanged($model);
        } catch (\Exception $exception) {
            $this->_logger->logException($exception);
        }
    }

    /**
     * Triggered after webapi role change
     *
     * @param \Magento\Event\Observer $observer
     */
    public function afterWebapiRoleChange(\Magento\Event\Observer $observer)
    {
        try {
            $model = $observer->getEvent()->getObject();

            $this->_webapiEventHandler->roleChanged($model);
        } catch (\Exception $exception) {
            $this->_logger->logException($exception);
        }
    }
}
