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
namespace Magento\PubSub\Event;

class QueueHandler
{
    /**
     * @var \Magento\PubSub\Event\QueueReaderInterface
     */
    protected $_eventQueue;

    /**
     * @var \Magento\PubSub\Job\QueueWriterInterface
     */
    protected $_jobQueue;

    /**
     * @var \Magento\PubSub\Job\FactoryInterface
     */
    protected $_jobFactory;

    /**
     * @var \Magento\PubSub\Subscription\CollectionInterface
     */
    protected $_subscriptionSet;

    /**
     * Initialize the class
     *
     * @param \Magento\PubSub\Event\QueueReaderInterface $eventQueue
     * @param \Magento\PubSub\Job\QueueWriterInterface $jobQueue
     * @param \Magento\PubSub\Job\FactoryInterface $jobFactory
     * @param \Magento\PubSub\Subscription\CollectionInterface $subscriptionSet
     */
    public function __construct(\Magento\PubSub\Event\QueueReaderInterface $eventQueue,
        \Magento\PubSub\Job\QueueWriterInterface $jobQueue,
        \Magento\PubSub\Job\FactoryInterface $jobFactory,
        \Magento\PubSub\Subscription\CollectionInterface $subscriptionSet
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
                /** @var $job \Magento\PubSub\JobInterface */
                $job = $this->_jobFactory->create($subscription, $event);
                $this->_jobQueue->offer($job);
            }
            $event->complete();
            $event = $this->_eventQueue->poll();
        }
    }
}
