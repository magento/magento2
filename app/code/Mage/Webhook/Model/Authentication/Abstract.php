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
abstract class Mage_Webhook_Model_Authentication_Abstract implements Mage_Webhook_Model_Authentication_Interface
{
    const DOMAIN_HEADER = "Magento-Sender-Domain";

    abstract protected function _signRequest(Mage_Webhook_Model_Transport_Http_Request $request,
                                             Mage_Webhook_Model_Subscriber $subscriber);

    /**
     * Add the DOMAIN_HEADER to the $request
     * @param Mage_Webhook_Model_Transport_Http_Request $request
     */
    protected function _setDomainHeader(Mage_Webhook_Model_Transport_Http_Request $request)
    {
        $headers                      = $request->getHeaders();
        $headers[self::DOMAIN_HEADER] = $this->_getDomain();
        $request->setHeaders($headers);
    }

    /**
     * Sign outbound HTTP request
     *
     * @todo - we need to implement signing for WS-Security
     * @param Mage_Webhook_Model_Transport_Http_Request $request
     * @param Mage_Webhook_Model_Subscriber $subscriber
     * @return Mage_Webhook_Model_Transport_Http_Request
     */
    public function signRequest(Mage_Webhook_Model_Transport_Http_Request $request,
                                Mage_Webhook_Model_Subscriber $subscriber)
    {
        $this->_setDomainHeader($request);
        $this->_signRequest($request, $subscriber);
        return $request;
    }

    /**
     * An overridable method to get the domain name
     * @return mixed
     */
    protected function _getDomain()
    {
        return parse_url(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), PHP_URL_HOST);
    }
}
