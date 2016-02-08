<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Validator;

use Braintree\Transaction;
use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class ResponseValidator
 */
class ResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(ResultInterfaceFactory $resultFactory, SubjectReader $subjectReader)
    {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponseObject($validationSubject);

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
    protected function validateSuccess($response)
    {
        return property_exists($response, 'success') && $response->success === true;
    }

    /**
     * @param object $response
     * @return bool
     */
    protected function validateErrors($response)
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
            [Transaction::AUTHORIZED, Transaction::SUBMITTED_FOR_SETTLEMENT]
        );
    }
}
