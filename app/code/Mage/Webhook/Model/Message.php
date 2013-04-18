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
 * Model of a message to be sent out to an endpoint and serialized in our datastore
 *
 * @method array getOptions()
 * @method int getEventId()
 */
class Mage_Webhook_Model_Message extends Mage_Core_Model_Abstract implements Mage_Webhook_Model_Message_Interface
{
    const FORMAT_ENABLED = 'enabled';

    /**
     * @param string $mapping
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setMapping($mapping)
    {
        return $this->setData('mapping', $mapping);
    }

    /**
     * Takes array of headers in format
     * 'header_name' => 'header_value'
     *
     * @param array $headers
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setHeaders(array $headers)
    {
        return $this->setData('headers', $headers);
    }

    /**
     * @param string $body
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setBody($body)
    {
        return $this->setData('body', $body);
    }

    /**
     * @param string $responseBody
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setResponseBody($responseBody)
    {
        return $this->setData('response_body', $responseBody);
    }

    /**
     * @param $responseData
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setResponseData(array $responseData)
    {
        return $this->setData('response_data', $responseData);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->getData('headers');
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->getData('body');
    }

    /**
     * @return string
     */
    public function getResponseData()
    {
        return $this->getData('response_data');
    }

    /**
     * @return array
     */
    public function getResponseBody()
    {
        return $this->getData('response_body');
    }

    /**
     * @param $url
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setEndpointUrl($url)
    {
        return $this->setData('endpoint_url', $url);
    }

    /**
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->getData('endpoint_url');
    }

    /**
     * @return string
     */
    public function getMapping()
    {
        return $this->getData('mapping');
    }
}
