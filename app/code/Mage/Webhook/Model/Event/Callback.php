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

/**
 * Mage Webhook Callback event
 */
class Mage_Webhook_Model_Event_Callback implements Mage_Webhook_Model_Event_Interface
{

    protected $_status;

    protected $_mapping;

    protected $_bodyData;

    protected $_headers;

    protected $_options;

    protected $_topic;


    public function __construct($mapping, array $bodyData, array $headers, $topic,
                                $status = Mage_Webhook_Model_Event::PREPARING, array $options = array())
    {
        $this->_mapping = $mapping;
        $this->_bodyData = $bodyData;
        $this->_headers = $headers;
        $this->_topic = $topic;

        $this->_status = $status;
        $this->_options = $options;
    }

    /**
     * Returns the status code of the event. Status indicates if the event has been processed
     * or not.
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Returns the data content mapping. The mapping could be "default", "flat", etc.
     * Specifically, it details what mapping the body data is in.
     *
     * @return string
     */
    public function getMapping()
    {
        return $this->_mapping;
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
     * Returns a PHP array of mapped key-value pairs representing header names and values.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Returns a PHP array of mapped key-value pairs representing options for the event.
     * The options could be set for the transport to understand or some future message.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Returns a PHP string representing the topic of WebHook
     * @return string
     */
    public function getTopic()
    {
        return $this->_topic;
    }

}