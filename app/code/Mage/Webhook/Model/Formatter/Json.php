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
class Mage_Webhook_Model_Formatter_Json implements Mage_Webhook_Model_Formatter_Interface
{
    const CONTENT_TYPE = 'application/json';

    const FORMAT = "json";
    /**
     * @param Mage_Webhook_Model_Event_Interface $event
     * @return Mage_Webhook_Model_Message
     */
    public function format(Mage_Webhook_Model_Event_Interface $event) 
    {
        $message = $this->newMessage();
        $message->setMapping($event->getMapping());
        $headers = $event->getHeaders();
        $headers[Mage_Webhook_Model_Formatter_Interface::CONTENT_TYPE_HEADER] = self::CONTENT_TYPE;
        $bodyData = $event->getBodyData();
        $encodedData = json_encode($bodyData);
        if (false === $encodedData) {
            throw new LogicException("The data provided cannot be encoded as json.");
        }
        $message->setHeaders($headers);
        $message->setBody($encodedData);
        return $message;
    }

    /**
     * @param Mage_Webhook_Model_Message_Interface $message
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function decode(Mage_Webhook_Model_Message_Interface $message)
    {
        $message->setResponseData(
            json_decode($message->getResponseBody(), true)
        );
        return $message;
    }

    public function newMessage() {
        return Mage::getModel('Mage_Webhook_Model_Message');
    }
}