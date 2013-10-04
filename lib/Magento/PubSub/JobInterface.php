<?php
/**
 * Represents a Job that is used to process events and send messages asynchronously
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
namespace Magento\PubSub;

interface JobInterface
{
    /**
     * Status is assigned to newly created Job, identify that it is good to be sent to subscriber
     */
    const STATUS_READY_TO_SEND         = 0;

    /**
     * Status is assigned to the Job when queue handler pick it up for processing
     */
    const STATUS_IN_PROGRESS           = 1;

    /**
     * Status is assigned to the Job when queue handler successfully delivered the job to subscriber
     */
    const STATUS_SUCCEEDED             = 2;

    /**
     * Status is assigned to the Job when queue handler failed to delivered the job after N retries
     */
    const STATUS_FAILED                = 3;

    /**
     * Status is assigned to the Job when queue handler failed to delivered the job but will retry more
     */
    const STATUS_RETRY                 = 4;

    /**
     * Get the event this job is responsible for processing
     *
     * @return \Magento\PubSub\EventInterface|null
     */
    public function getEvent();

    /**
     * Return the subscription to send a message to
     *
     * @return \Magento\PubSub\SubscriptionInterface|null
     */
    public function getSubscription();

    /**
     * Update the Job status to indicate it has completed successfully
     *
     * @return \Magento\PubSub\JobInterface
     */
    public function complete();

    /**
     * Handle retry on failure logic and update job status accordingly.
     *
     * @return \Magento\PubSub\JobInterface
     */
    public function handleFailure();

    /**
     * Retrieve the status of the Job
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set the status of the Job
     *
     * @param int $status
     * @return \Magento\PubSub\JobInterface
     */
    public function setStatus($status);
}
