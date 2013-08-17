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
interface Magento_PubSub_JobInterface
{
    /**
     * Status codes for job
     */
    const READY_TO_SEND         = 0;
    const SUCCESS               = 1;
    const FAILED                = 2;
    const RETRY                 = 3;

    /**
     * Get the event this job is responsible for processing
     *
     * @return Magento_PubSub_EventInterface|null
     */
    public function getEvent();

    /**
     * Return the subscription to send a message to
     *
     * @return Magento_PubSub_SubscriptionInterface|null
     */
    public function getSubscription();

    /**
     * Process response and update Job status accordingly.
     *
     * @param Magento_Outbound_Transport_Http_Response $response
     */
    public function handleResponse($response);

    /**
     * Handle retry on failure logic and update job status accordingly.
     */
    public function handleFailure();
}