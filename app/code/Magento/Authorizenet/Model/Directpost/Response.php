<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\Directpost;

use Magento\Authorizenet\Model\Response as AuthorizenetResponse;
use Magento\Framework\Encryption\Helper\Security;

/**
 * Authorize.net response model for DirectPost model
 */
class Response extends AuthorizenetResponse
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
    public function generateHash($merchantMd5, $merchantApiLogin, $amount, $transactionId)
    {
        return strtoupper(md5($merchantMd5 . $merchantApiLogin . $transactionId . $amount));
    }

    /**
     * Return if is valid order id.
     *
     * @param string $storedHash
     * @param string $merchantApiLogin
     * @return bool
     */
    public function isValidHash($storedHash, $merchantApiLogin)
    {
        if (empty($this->getData('x_amount'))) {
            $this->setData('x_amount', '0.00');
        }

        if (!empty($this->getData('x_SHA2_Hash'))) {
            $hash = $this->generateSha2Hash($storedHash);
            return Security::compareStrings($hash, $this->getData('x_SHA2_Hash'));
        } elseif (!empty($this->getData('x_MD5_Hash'))) {
            $hash = $this->generateHash($storedHash, $merchantApiLogin, $this->getXAmount(), $this->getXTransId());
            return Security::compareStrings($hash, $this->getData('x_MD5_Hash'));
        }

        return false;
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

    /**
     * Generates an SHA2 hash to compare against AuthNet's.
     *
     * @param string $signatureKey
     * @return string
     * @see https://support.authorize.net/s/article/MD5-Hash-End-of-Life-Signature-Key-Replacement
     */
    private function generateSha2Hash($signatureKey)
    {
        $hashFields = [
            'x_trans_id',
            'x_test_request',
            'x_response_code',
            'x_auth_code',
            'x_cvv2_resp_code',
            'x_cavv_response',
            'x_avs_code',
            'x_method',
            'x_account_number',
            'x_amount',
            'x_company',
            'x_first_name',
            'x_last_name',
            'x_address',
            'x_city',
            'x_state',
            'x_zip',
            'x_country',
            'x_phone',
            'x_fax',
            'x_email',
            'x_ship_to_company',
            'x_ship_to_first_name',
            'x_ship_to_last_name',
            'x_ship_to_address',
            'x_ship_to_city',
            'x_ship_to_state',
            'x_ship_to_zip',
            'x_ship_to_country',
            'x_invoice_num',
        ];

        $message = '^';
        foreach ($hashFields as $field) {
            if (!empty($this->getData($field))) {
                $message .= $this->getData($field);
            }
            $message .= '^';
        }

        return strtoupper(hash_hmac('sha512', $message, pack('H*', $signatureKey)));
    }
}
