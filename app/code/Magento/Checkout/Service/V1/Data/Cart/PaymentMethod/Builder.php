<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Data\Cart\PaymentMethod;

use Magento\Checkout\Service\V1\Data\Cart\PaymentMethod as QuotePaymentMethod;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Quote;

class Builder
{
    /**
     * @param QuotePaymentMethod $object
     * @param Quote $quote
     * @return \Magento\Sales\Model\Quote\Payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(QuotePaymentMethod $object, Quote $quote)
    {
        $payment = $quote->getPayment();
        try {
            $data = $object->__toArray();
            $additionalDataValue = $object->getPaymentDetails();
            unset($data[QuotePaymentMethod::PAYMENT_DETAILS]);
            if (!empty($additionalDataValue)) {
                $additionalData = @unserialize($additionalDataValue);
                if (is_array($additionalData) && !empty($additionalData)) {
                    $data = array_merge($data, $additionalData);
                }
            }
            $data['checks'] = [
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ];
            $payment->importData($data);
        } catch (\Exception $e) {
            throw new LocalizedException('The requested Payment Method is not available.');
        }
        return $payment;
    }
}
