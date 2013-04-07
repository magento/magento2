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
class Mage_Webhook_Model_Job_Processor
{
    /**
     * Creates jobs from the events in the queue. A job associates an event with a subscriber.
     *
     * @param Mage_Webhook_Model_Event_Queue $queue
     */
    public function createJobsFromQueue(Mage_Webhook_Model_Event_Queue $queue)
    {
        while (($event = $queue->poll()) != null) {
            $this->createJobs($event);
        }
    }

    /**
     * Creates a job from the event parameter. A job associates an event with a subscriber.
     *
     * @param Mage_Webhook_Model_Event $event
     */
    public function createJobs(Mage_Webhook_Model_Event $event)
    {
        $topic = $event->getTopic();

        $subscriberCollection = $this->getResourceSubscriberCollection()->addTopicFilter($topic)
            ->addMappingFilter($event->getMapping())
            ->addIsActiveFilter(true);

        if (!empty($subscriberCollection)) {
            foreach ($subscriberCollection as $subscriber) {
                $job = $this->_newDispatchJob()
                    ->setSubscriberId($subscriber->getId())
                    ->setEventId($event->getId())
                    ->setStatus(Mage_Webhook_Model_Dispatch_Job::READY_TO_SEND);
                $job->save();
            }
        }
    }

    public function getResourceSubscriberCollection() {
        return Mage::getResourceModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
    }

    /**
     * Returns an instance of a dispatch job.
     *
     * @return Mage_Webhook_Model_Dispatch_Job
     */
    protected function _newDispatchJob()
    {
        return Mage::getModel('Mage_Webhook_Model_Dispatch_Job');
    }
}
