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

abstract class Mage_Webhook_Model_Authentication_Hmac extends Mage_Webhook_Model_Authentication_Abstract
{
    const SHARED_SECRET_LENGTH = 32;

    const HMAC_HEADER = 'Magento-HMAC-Signature';

    abstract public function getHashAlgorithm();

    /**
     * Sign the $request using HMAC with the hash algorithm provided by getHashAlgorithm()
     * @param Mage_Webhook_Model_Transport_Http_Request $request
     * @param Mage_Webhook_Model_Subscriber $subscriber
     * @return Mage_Webhook_Model_Transport_Http_Request
     */
    protected function _signRequest(Mage_Webhook_Model_Transport_Http_Request$request,
                                    Mage_Webhook_Model_Subscriber $subscriber)
    {
        $secret = $subscriber->getApiUser()->getSecret();
        if ('' === $secret || is_null($secret)) {
            throw new LogicException("The shared secret cannot be a empty.");
        }
        // Add HMAC Signature
        $this->_signRequestHmac($request, $secret, $this->getHashAlgorithm());

        return $request;
    }

    /**
     * @param Mage_Webhook_Model_Transport_Http_Request $request
     * @param string $hashAlgorithm
     */
    protected function _signRequestHmac(Mage_Webhook_Model_Transport_Http_Request $request, $secret,
                                        $hashAlgorithm)
    {
        $body = $request->getBody();

        $signature = hash_hmac($hashAlgorithm, $body, $secret);

        $headers                    = $request->getHeaders();
        $headers[self::HMAC_HEADER] = $signature;
        $request->setHeaders($headers);
        return $request;
    }
}
