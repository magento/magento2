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

class Event implements \Magento\PubSub\EventInterface
{
    /** @var int */
    protected $_status = \Magento\PubSub\EventInterface::STATUS_READY_TO_SEND;

    /** @var array */
    protected $_bodyData;

    /** @var array */
    protected $_headers = array();

    /** @var string */
    protected $_topic;

    /**
     * @param $topic
     * @param $bodyData
     */
    public function __construct($topic, $bodyData)
    {
        $this->_topic = $topic;
        $this->_bodyData = $bodyData;
    }

    /**
     * Returns the status code of the event. Status indicates if the event has been processed
     * or not.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Returns a PHP array of data that represents what should be included in the message body.
     *
     * @return array
     */
    public function getBodyData()
    {
        return $this->_bodyData;
    }

    /**
     * Prepare headers before return
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Returns a PHP string representing the topic of WebHook
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->_topic;
    }

    /**
     * Mark event as processed
     *
     * @return \Magento\PubSub\Event
     */
    public function complete()
    {
        $this->_status = \Magento\PubSub\EventInterface::STATUS_PROCESSED;
        return $this;
    }

    /**
     * Mark event as processed
     *
     * @return \Magento\PubSub\Event
     */
    public function markAsInProgress()
    {
        $this->_status = \Magento\PubSub\EventInterface::STATUS_IN_PROGRESS;
        return $this;
    }
}
