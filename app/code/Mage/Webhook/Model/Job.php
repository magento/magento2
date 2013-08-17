<?php
/**
 * Handles HTTP responses, and manages retry schedule
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
 *
 * @method bool hasEvent()
 * @method Mage_Webhook_Model_Job setEventId()
 * @method int getEventId()
 * @method bool hasSubscription()
 * @method Mage_Webhook_Model_Job setSubscriptionId()
 * @method int getSubscriptionId()
 * @method Mage_Webhook_Model_Job setStatus()
 * @method int getRetryCount()
 * @method Mage_Webhook_Model_Job setRetryCount()
 * @method Mage_Webhook_Model_Job setRetryAt()
 * @method Mage_Webhook_Model_Job setUpdatedAt()
 */
class Mage_Webhook_Model_Job extends Mage_Core_Model_Abstract implements Magento_PubSub_JobInterface
{
    /** @var  Mage_Webhook_Model_Event_Factory */
    protected $_eventFactory;

    /** @var Mage_Webhook_Model_Subscription_Factory */
    protected $_subscriptionFactory;

    /** @var array */
    private $_retryTimeToAdd = array(
        1 => 1,
        2 => 2,
        3 => 4,
        4 => 10,
        5 => 30,
        6 => 60,
        7 => 120,
        8 => 240,
    );

    /**
     * @param Mage_Webhook_Model_Event_Factory $eventFactory
     * @param Mage_Webhook_Model_Subscription_Factory $subscriptionFactory
     * @param Mage_Core_Model_Context $context
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Varien_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Mage_Webhook_Model_Event_Factory $eventFactory,
        Mage_Webhook_Model_Subscription_Factory $subscriptionFactory,
        Mage_Core_Model_Context $context,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_subscriptionFactory = $subscriptionFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Webhook_Model_Resource_Job');

        if ($this->hasEvent()) {
            $this->setEventId($this->getEvent()->getId());
        }

        if ($this->hasSubscription()) {
            $this->setSubscriptionId($this->getSubscription()->getId());
        }
    }

    /**
     * Get event
     *
     * @return Magento_PubSub_EventInterface|Mage_Webhook_Model_Event|null
     */
    public function getEvent()
    {
        if ($this->hasData('event')) {
            return $this->getData('event');
        }

        if ($this->hasData('event_id')) {
            $event = $this->_eventFactory->createEmpty()
                ->load($this->getEventId());
            $this->setData('event', $event);
            return $event;
        }

        return null;
    }

    /**
     * Get subscription
     *
     * @return Mage_Webhook_Model_Subscription|null
     */
    public function getSubscription()
    {
        if ($this->hasData('subscription')) {
            return $this->getData('subscription');
        }

        if ($this->hasData('subscription_id')) {
            $subscription = $this->_subscriptionFactory->create()
                ->load($this->getSubscriptionId());

            $this->setData('subscription', $subscription);
            return $subscription;
        }

        return null;
    }

    /**
     * Handles HTTP response
     *
     * @param Magento_Outbound_Transport_Http_Response $response
     */
    public function handleResponse($response)
    {
        if ($response->isSuccessful()) {
            $this->setStatus(Magento_PubSub_JobInterface::SUCCESS);
        } else {
            $this->handleFailure();
        }
        $this->save();
    }

    /**
     * Handles failed HTTP response
     */
    public function handleFailure()
    {
        $retryCount = $this->getRetryCount();
        if ($retryCount < count($this->_retryTimeToAdd)) {
            $addedTimeInMinutes = $this->_retryTimeToAdd[$retryCount + 1] * 60 + time();
            $this->setRetryCount($retryCount + 1);
            $this->setRetryAt(Varien_Date::formatDate($addedTimeInMinutes));
            $this->setUpdatedAt(Varien_Date::formatDate(time(), true));
            $this->setStatus(Magento_PubSub_JobInterface::RETRY);
        } else {
            $this->setStatus(Magento_PubSub_JobInterface::FAILED);
        }
    }
}