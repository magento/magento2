<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Validator;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class GeneralResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var ErrorCodeValidator
     */
    private $errorCodeValidator;

    /**
     * Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param ErrorCodeValidator $errorCodeValidator
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader,
        ErrorCodeValidator $errorCodeValidator
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->errorCodeValidator = $errorCodeValidator;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        /** @var Successful|Error $response */
        $response = $this->subjectReader->readResponseObject($validationSubject);

        $isValid = true;
        $errorMessages = [];

        foreach ($this->getResponseValidators() as $validator) {
            $validationResult = $validator($response);

            if (!$validationResult[0]) {
                $isValid = $validationResult[0];
                $errorMessages = array_merge($errorMessages, $validationResult[1]);
            }
        }

        return $this->createResult($isValid, $errorMessages);
    }

    /**
     * @return array
     */
    protected function getResponseValidators()
    {
        return [
            function ($response) {
                return [
                    property_exists($response, 'success') && $response->success === true,
                    [$response->message ?? __('Braintree error response.')]
                ];
            },
            $this->errorCodeValidator
        ];
    }
}
