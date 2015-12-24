<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Validator;

use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;

/**
 * Class ResponseValidator
 * @package Magento\BraintreeTwo\Gateway\Validator
 */
class ResponseValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponseObject($validationSubject);

        $result = $this->createResult(
            $this->validateSuccess($response)
            && $this->validateErrors($response)
            && $this->validateTransactionStatus($response),
            [__('Transaction has been declined. Please try again later.')]
        );

        return $result;
    }

    /**
     * @param object $response
     * @return bool
     */
    private function validateSuccess($response)
    {
        return property_exists($response, 'success') && $response->success === true;
    }

    /**
     * @param object $response
     * @return bool
     */
    private function validateErrors($response)
    {
        return !(property_exists($response, 'errors') && $response->errors->deepSize() > 0);
    }

    /**
     * @param object $response
     * @return bool
     */
    private function validateTransactionStatus($response)
    {
        return in_array(
            $response->transaction->status,
            [\Braintree_Transaction::AUTHORIZED, \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT]
        );
    }
}
