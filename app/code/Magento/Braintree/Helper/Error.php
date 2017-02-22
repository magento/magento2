<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Helper;

use \Braintree_Result_Error;

class Error extends \Magento\Framework\App\Helper\AbstractHelper
{
    const STATUS_GATEWAY_REJECTED   = 'gateway_rejected';
    const STATUS_PROCESSOR_DECLINED = 'processor_declined';
    const CLONE_ERROR_CODE          = '91542';
    const NONCE_USED_MORE_THAN_ONCE = '91564';

    /**
     * @var array
     */
    protected $_codesMessages = [
        2000 => 'Contact your bank or try another card',
        2001 => 'Contact your bank or try another card',
        2002 => 'Contact your bank or try another card',
        2003 => 'Contact your bank or try another card',
        2004 => 'Check card details or try another card',
        2005 => 'Check card details or try another card',
        2006 => 'Check card details or try another card',
        2007 => 'Check card details or try another card',
        2008 => 'Check card details or try another card',
        2009 => 'Try another card',
        2010 => 'Check card details or try another card',
        2011 => 'Voice Authorization Required',
        2012 => 'Contact your bank or try another card',
        2013 => 'Contact your bank or try another card',
        2014 => 'Contact your bank or try another card',
        2015 => 'Contact your bank or try another card',
        2016 => 'Duplicate transaction',
        2017 => 'Contact your bank or try another card',
        2018 => 'Contact your bank or try another card',
        2019 => 'Contact your bank or try another card',
        2020 => 'Contact your bank or try another card',
        2021 => 'Contact your bank or try another card',
        2022 => 'Contact your bank or try another card',
        2023 => 'Try another card',
        2024 => 'Try another card',
        2025 => 'Try again later',
        2026 => 'Try again later',
        2027 => 'Try again later',
        2028 => 'Try again later',
        2029 => 'Try again later',
        2030 => 'Try again later',
        2031 => 'Try another card',
        2032 => 'Try another card',
        2033 => 'Try another card',
        2034 => 'Try another card',
        2035 => 'Try another card',
        2036 => 'Try another card',
        2037 => 'Try another card',
        2038 => 'Contact your bank or try another card',
        2039 => 'Try another card',
        2040 => 'Try another card',
        2041 => 'Contact your bank or try another card',
        2043 => 'Contact your bank or try another card',
        2044 => 'Contact your bank or try another card',
        2045 => 'Try again later',
        2046 => 'Contact your bank or try another card',
        2047 => 'Try another card',
        2048 => 'Try again later',
        2049 => 'Try again later',
        2050 => 'Try again later',
        2051 => 'Check card details or try another card',
        2052 => 'Try again later',
        2053 => 'Try another card',
        2054 => 'Processor decline',
        2055 => 'Processor decline',
        2056 => 'Processor decline',
        2057 => 'Contact your bank or try another card',
        2058 => 'Try another card',
        2059 => 'Contact your bank or try another card',
        2060 => 'Contact your bank or try another card',
        2061 => 'Processor decline',
        2062 => 'Processor decline',
        3000 => 'Processor network error',
    ];

    /**
     * Parses unsuccessful result into message
     *
     * @param \Braintree_Result_Error $result
     * @return \Magento\Framework\Phrase
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function parseBraintreeError($result)
    {
        $message = null;
        if ($this->isCreditCardApiError($result)) {
            $message = __($this->isCreditCardApiError($result));
        } elseif ($this->isNonceUsedMoreThanOnceError($result)) {
            $message = __("The processor declined your transaction, please re-enter your payment information");
        } elseif (isset($result->transaction) && $result->transaction && $result->transaction->status) {
            if ($result->transaction->status == self::STATUS_GATEWAY_REJECTED) {
                $message = __('Transaction declined by gateway: Check card details or try another card');
            } else if ($result->transaction->status == self::STATUS_PROCESSOR_DECLINED) {
                if (isset($result->transaction->processorResponseCode) && $result->transaction->processorResponseCode) {
                    $code = $result->transaction->processorResponseCode;
                    if (array_key_exists($code, $this->_codesMessages)) {
                        $prefix = __('Transaction declined: ')->getText();
                        $message = __($prefix . __($this->_codesMessages[$code])->getText());
                    }
                }
            }
        }
        if (!is_object($message)) {
            $errors = explode("\n", $result->message);
            $message = '';
            foreach ($errors as $error) {
                $message .= rtrim(' ' . __($error)->getText().', ', ', ');
            }
            $message = __($message);
        }
        if (!is_object($message)) {
            $message = __("The processor responded with an unknown error");
        }
        return $message;
    }

    /**
     * If result error is "Unsuccessful transaction cannot be cloned."
     * 
     * @param \Braintree_Result_Error $result
     * @return boolean
     */
    public function isCloneUnsuccessfulError($result)
    {
        $errors = $result->errors->deepAll();
        foreach ($errors as $error) {
            if ($error->code  == self::CLONE_ERROR_CODE) {
                return true;
            }
        }
        return false;
    }

    /**
     * If result error is "Unsuccessful transaction nonce can't be used more than once."
     *
     * @param \Braintree_Result_Error $result
     * @return boolean
     */
    public function isNonceUsedMoreThanOnceError($result)
    {
        $errors = $result->errors->deepAll();
        foreach ($errors as $error) {
            if ($error->code  == self::NONCE_USED_MORE_THAN_ONCE) {
                return true;
            }
        }
        return false;
    }

    /**
     * If result error is related to input validation
     *
     * @param \Braintree_Result_Error $result
     * @return boolean
     */
    public function isCreditCardApiError($result)
    {
        $errors = $result->errors->deepAll();
        foreach ($errors as $error) {
            if (($error->code > 80000) && ($error->code < 90000)) {
                return $error->message;
            }
        }
        return false;
    }
}
