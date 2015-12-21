<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Validator;

/**
 * Class PaymentNonceResponseValidator
 */
class PaymentNonceResponseValidator extends ResponseValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponseObject($validationSubject);
        $result = $this->createResult(
            $this->validateSuccess($response) &&
            $this->validateErrors($response) &&
            $this->validatePaymentMethodNonce($response),
            [__('Payment method nonce can\'t be retrieved.')]
        );

        return $result;
    }

    /**
     * Validate payment method nonce of response
     *
     * @param object $response
     * @return bool
     */
    private function validatePaymentMethodNonce($response)
    {
        return !empty($response->paymentMethodNonce) && !empty($response->paymentMethodNonce->nonce);
    }
}
