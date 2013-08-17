<?php
/**
 * Custom Magento implementation of Job Queue Writer interface, writes jobs to database based queue
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
class Mage_Webhook_Model_Job_QueueWriter implements Magento_PubSub_Job_QueueWriterInterface
{
    /** @var Mage_Webhook_Model_Job_Factory */
    protected $_jobFactory;

    /**
     * Initialize model
     *
     * @param Mage_Webhook_Model_Job_Factory $jobFactory
     */
    public function __construct(Mage_Webhook_Model_Job_Factory $jobFactory)
    {
        $this->_jobFactory = $jobFactory;
    }

    /**
     * Adds the job to the queue.
     *
     * @param Magento_PubSub_JobInterface $job
     * @return null
     */
    public function offer(Magento_PubSub_JobInterface $job)
    {
        if ($job instanceof Mage_Webhook_Model_Job) {
            $job->save();
        } else {
            /** @var Mage_Webhook_Model_Job $magentoJob */
            $magentoJob = $this->_jobFactory->create($job->getSubscription(), $job->getEvent());
            $magentoJob->save();
        }
    }
}
