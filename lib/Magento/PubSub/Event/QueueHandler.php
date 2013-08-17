<?php
/**
 * Handles event queue, uses it to build job queue
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
 * @package     Magento_PubSub
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_PubSub_Event_QueueHandler
{
    /**
     * @var Magento_PubSub_Event_QueueReaderInterface
     */
    protected $_eventQueue;

    /**
     * @var Magento_PubSub_Job_QueueWriterInterface
     */
    protected $_jobQueue;

    /**
     * @var Magento_PubSub_Job_FactoryInterface
     */
    protected $_jobFactory;

    /**
     * @var Magento_PubSub_Subscription_CollectionInterface
     */
    protected $_subscriptionSet;

    /**
     * Initialize the class
     *
     * @param Magento_PubSub_Event_QueueReaderInterface $eventQueue
     * @param Magento_PubSub_Job_QueueWriterInterface $jobQueue
     * @param Magento_PubSub_Job_FactoryInterface $jobFactory
     * @param Magento_PubSub_Subscription_CollectionInterface $subscriptionSet
     */
    public function __construct(Magento_PubSub_Event_QueueReaderInterface $eventQueue,
        Magento_PubSub_Job_QueueWriterInterface $jobQueue,
        Magento_PubSub_Job_FactoryInterface $jobFactory,
        Magento_PubSub_Subscription_CollectionInterface $subscriptionSet
    ) {
        $this->_eventQueue = $eventQueue;
        $this->_jobQueue = $jobQueue;
        $this->_jobFactory = $jobFactory;
        $this->_subscriptionSet = $subscriptionSet;
    }

    /**
     * Build job queue from event queue
     */
    public function handle()
    {
        $event = $this->_eventQueue->poll();
        while (!is_null($event)) {
            $subscriptions = $this->_subscriptionSet->getSubscriptionsByTopic($event->getTopic());
            foreach ($subscriptions as $subscription) {
                /** @var $job Magento_PubSub_JobInterface */
                $job = $this->_jobFactory->create($subscription, $event);
                $this->_jobQueue->offer($job);
            }
            $event = $this->_eventQueue->poll();
        }
    }
}