<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Validator;

/**
 * Class PaymentNonceResponseValidator
 */
class PaymentNonceResponseValidator extends GeneralResponseValidator
{
    /**
     * @return array
     */
    protected function getResponseValidators()
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                function ($response) {
                    return [
                        !empty($response->paymentMethodNonce) && !empty($response->paymentMethodNonce->nonce),
                        [__('Payment method nonce can\'t be retrieved.')]
                    ];
                }
            ]
        );
    }
}
