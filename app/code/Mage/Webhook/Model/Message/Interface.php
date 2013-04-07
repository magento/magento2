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
interface Mage_Webhook_Model_Message_Interface
{
    /**
     * @param string $mapping
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setMapping($mapping);

    /**
     * Takes array of headers in format
     * 'header_name' => 'header_value'
     *
     * @param array $headers
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setHeaders(array $headers);

    /**
     * @param string $body
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setBody($body);

    /**
     * @param string $responseBody
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setResponseBody($responseBody);

    /**
     * @param $responseData
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setResponseData(array $responseData);

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @return string
     */
    public function getBody();

    /**
     * @return string
     */
    public function getResponseData();

    /**
     * @return array
     */
    public function getResponseBody();

    /**
     * @param $url
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function setEndpointUrl($url);

    /**
     * @return string
     */
    public function getEndpointUrl();

    /**
     * @return string
     */
    public function getMapping();

}