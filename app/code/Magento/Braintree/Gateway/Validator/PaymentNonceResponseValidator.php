<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Validator;

/**
 * Class PaymentNonceResponseValidator
 * @since 2.1.0
 */
class PaymentNonceResponseValidator extends GeneralResponseValidator
{
    /**
     * @return array
     * @since 2.1.0
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
