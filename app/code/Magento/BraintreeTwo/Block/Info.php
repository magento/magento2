<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Block;

use Magento\BraintreeTwo\Gateway\Response\CardDetailsHandler;
use Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler;
use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class Info extends ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getLabel($field)
    {
        switch ($field) {
            case OrderPaymentInterface::CC_TYPE:
                return __('Credit Card Type');
            case CardDetailsHandler::CARD_NUMBER:
                return __('Credit Card Number');
            case PaymentDetailsHandler::AVS_POSTAL_RESPONSE_CODE:
                return __('AVS Postal Code Response Code');
            case PaymentDetailsHandler::AVS_STREET_ADDRESS_RESPONSE_CODE:
                return __('Avs Street Address Response Code');
            case PaymentDetailsHandler::CVV_RESPONSE_CODE:
                return __('Cvv Response Code');
            case PaymentDetailsHandler::PROCESSOR_AUTHORIZATION_CODE:
                return __('Processor Authorization Code');
            case PaymentDetailsHandler::PROCESSOR_RESPONSE_CODE:
                return __('Processor Response Code');
            case PaymentDetailsHandler::PROCESSOR_RESPONSE_TEXT:
                return __('Processor Response Text');
            default:
                return parent::getLabel($field);
        }
    }
}
