<?php
/**
 * Represents a PubSub event to be dispatched
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

interface EventInterface
{
    /**
     * Status is assigned to newly created Event, identify that it is good to be sent to subscribers
     */
    const STATUS_READY_TO_SEND = 0;

    /**
     * Status is assigned to event when queue handler pick it up for processing
     */
    const STATUS_IN_PROGRESS   = 1;

    /**
     * Status is assigned to event when queue handler successfully processed the event
     */
    const STATUS_PROCESSED     = 2;

    /**
     * Returns the status code of the event. Status indicates if the event has been processed
     * or not.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Returns a PHP array of data that represents what should be included in the message body.
     *
     * @return array
     */
    public function getBodyData();

    /**
     * Prepare headers before return
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Returns a PHP string representing the topic of WebHook
     *
     * @return string
     */
    public function getTopic();

    /**
     * Mark event as processed
     *
     * @return \Magento\PubSub\EventInterface
     */
    public function complete();

    /**
     * Mark event as In Progress
     *
     * @return \Magento\PubSub\Event
     */
    public function markAsInProgress();
}
