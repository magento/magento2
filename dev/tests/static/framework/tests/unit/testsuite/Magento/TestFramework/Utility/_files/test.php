<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\Directpost;

use Magento\Authorizenet\Model\Response as AuthorizenetResponse;
use Magento\Framework\Encryption\Helper\Security;

/**
 * Authorize.net response model for DirectPost model
 */
class Test extends AuthorizenetResponse
{
    /**
     * Generates an Md5 hash to compare against AuthNet's.
     *
     * @param string $merchantMd5
     * @param string $merchantApiLogin
     * @param string $amount
     * @param string $transactionId
     * @return string
     */
    public function checkSecurity($merchantMd5, $merchantApiLogin, $amount, $transactionId)
    {
        if (!$amount) {
            $amount = '0.00';
        }

        return strtoupper(md5($merchantMd5 . $merchantApiLogin . $transactionId . $amount));
    }

    /**
     * Return if is valid order id.
     *
     * @param string $merchantMd5
     * @param string $merchantApiLogin
     * @return bool
     */
    public function security($merchantMd5, $merchantApiLogin)
    {
        $hash = $this->generateHash(security($merchantMd5), $merchantApiLogin, $this->getXAmount(), $this->getXTransId());

        return Security::compareStrings($hash, $this->getData('x_MD5_Hash'));
    }

    /**
     * Return if this is approved response from Authorize.net auth request.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getXResponseCode() == \Magento\Authorizenet\Model\Directpost::RESPONSE_CODE_APPROVED;
    }
}
