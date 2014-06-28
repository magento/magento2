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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model\Api;

class ProcessableException extends \Magento\Framework\Model\Exception
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
    /**#@-*/

    /**
     * Get error message which can be displayed to website user
     *
     * @return string
     */
    public function getUserMessage()
    {
        switch ($this->getCode()) {
            case self::API_INTERNAL_ERROR:
            case self::API_UNABLE_PROCESS_PAYMENT_ERROR_CODE:
                $message = __("I'm sorry - but we were not able to process your payment. Please try another payment method or contact us so we can assist you.");
                break;
            case self::API_COUNTRY_FILTER_DECLINE:
            case self::API_MAXIMUM_AMOUNT_FILTER_DECLINE:
            case self::API_OTHER_FILTER_DECLINE:
                $message = __("I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you.");
                break;
            default:
                $message = $this->getMessage();
        }
        return $message;
    }
}
