<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * @api
 */
class ProcessableException extends LocalizedException
{
    /**#@+
     * Error code returned by PayPal
     */
    const API_INTERNAL_ERROR = 10001;
    const API_UNABLE_PROCESS_PAYMENT_ERROR_CODE = 10417;
    const API_MAX_PAYMENT_ATTEMPTS_EXCEEDED = 10416;
    const API_UNABLE_TRANSACTION_COMPLETE = 10486;
    const API_TRANSACTION_EXPIRED = 10411;
    const API_DO_EXPRESS_CHECKOUT_FAIL = 10422;
    const API_COUNTRY_FILTER_DECLINE = 10537;
    const API_MAXIMUM_AMOUNT_FILTER_DECLINE = 10538;
    const API_OTHER_FILTER_DECLINE = 10539;
    const API_ADDRESS_MATCH_FAIL = 10736;
    /**#@-*/

    /**
     * Constructor
     *
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase, \Exception $cause = null, $code = 0)
    {
        parent::__construct($phrase, $cause);
        $this->code = $code;
    }

    /**
     * Get error message which can be displayed to website user
     *
     * @return \Magento\Framework\Phrase
     */
    public function getUserMessage()
    {
        switch ($this->getCode()) {
            case self::API_INTERNAL_ERROR:
            case self::API_UNABLE_PROCESS_PAYMENT_ERROR_CODE:
                $message = __(
                    'I\'m sorry - but we were not able to process your payment. Please try another payment method or contact us so we can assist you.'
                );
                break;
            case self::API_COUNTRY_FILTER_DECLINE:
            case self::API_MAXIMUM_AMOUNT_FILTER_DECLINE:
            case self::API_OTHER_FILTER_DECLINE:
                $message = __(
                    'I\'m sorry - but we are not able to complete your transaction. Please contact us so we can assist you.'
                );
                break;
            case self::API_ADDRESS_MATCH_FAIL:
                $message = __(
                    'A match of the Shipping Address City, State, and Postal Code failed.'
                );
                break;
            default:
                $message = __('We can\'t place the order.');
                break;
        }
        return $message;
    }
}
