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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method bool hasEvent()
 * @method \Magento\Webhook\Model\Job setEventId()
 * @method int getEventId()
 * @method bool hasSubscription()
 * @method \Magento\Webhook\Model\Job setSubscriptionId()
 * @method int getSubscriptionId()
 * @method int getRetryCount()
 * @method \Magento\Webhook\Model\Job setRetryCount()
 * @method \Magento\Webhook\Model\Job setRetryAt()
 * @method \Magento\Webhook\Model\Job setUpdatedAt()
 * @method \Magento\Webhook\Model\Job setCreatedAt()
 */
namespace Magento\Webhook\Model;

class Job extends \Magento\Core\Model\AbstractModel implements \Magento\PubSub\JobInterface
{
    /** @var  \Magento\Webhook\Model\Event\Factory */
    protected $_eventFactory;

    /** @var \Magento\Webhook\Model\Subscription\Factory */
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
     * @param \Magento\Webhook\Model\Event\Factory $eventFactory
     * @param \Magento\Webhook\Model\Subscription\Factory $subscriptionFactory
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Webhook\Model\Event\Factory $eventFactory,
        \Magento\Webhook\Model\Subscription\Factory $subscriptionFactory,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_subscriptionFactory = $subscriptionFactory;
        parent::__construct($context, $coreRegistry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Webhook\Model\Resource\Job');

        if ($this->hasEvent()) {
            $this->setEventId($this->getEvent()->getId());
        }

        if ($this->hasSubscription()) {
            $this->setSubscriptionId($this->getSubscription()->getId());
        }
        $this->setStatus(\Magento\PubSub\JobInterface::STATUS_READY_TO_SEND);
    }

    /**
     * Prepare data to be saved to database
     *
     * @return \Magento\Webhook\Model\Job
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($this->_getResource()->formatDate(true));
        } elseif ($this->getId() && !$this->hasData('updated_at')) {
            $this->setUpdatedAt($this->_getResource()->formatDate(true));
        }
        return $this;
    }

    /**
     * Get event
     *
     * @return \Magento\PubSub\EventInterface|\Magento\Webhook\Model\Event|null
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
     * @return \Magento\Webhook\Model\Subscription|null
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
     * Update the Job status to indicate it has completed successfully
     *
     * @return \Magento\Webhook\Model\Job
     */
    public function complete()
    {
        $this->setStatus(\Magento\PubSub\JobInterface::STATUS_SUCCEEDED)
            ->save();
        return $this;
    }

    /**
     * Handles failed HTTP response
     *
     * @return \Magento\Webhook\Model\Job
     */
    public function handleFailure()
    {
        $retryCount = $this->getRetryCount();
        if ($retryCount < count($this->_retryTimeToAdd)) {
            $addedTimeInMinutes = $this->_retryTimeToAdd[$retryCount + 1] * 60 + time();
            $this->setRetryCount($retryCount + 1);
            $this->setRetryAt(\Magento\Date::formatDate($addedTimeInMinutes));
            $this->setUpdatedAt(\Magento\Date::formatDate(time(), true));
            $this->setStatus(\Magento\PubSub\JobInterface::STATUS_RETRY);
        } else {
            $this->setStatus(\Magento\PubSub\JobInterface::STATUS_FAILED);
        }
        return $this;
    }

    /**
     * Retrieve the status of the Job
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData('status');
    }

    /**
     * Set the status of the Job
     *
     * @param int $status
     * @return \Magento\Webhook\Model\Job
     */
    public function setStatus($status)
    {
        $this->setData('status', $status);
        return $this;
    }
}
