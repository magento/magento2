<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Validator;

/**
 * Class PaymentNonceResponseValidator
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
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
