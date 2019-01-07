<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Validates that the request was successful
 */
class GeneralResponseValidator extends AbstractValidator
{
    /**
     * The result code that authorize.net returns for a successful Api call
     */
    const RESULT_CODE_SUCCESS = 'Ok';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->resultFactory = $resultFactory;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);
        $isValid = (isset($response['messages']['result_code'])
            && $response['messages']['resultCode'] === self::RESULT_CODE_SUCCESS);
        $errorCodes = [];
        $errorMessages = [];

        if (!$isValid) {
            foreach ($response['messages']['message'] as $message) {
                $errorCodes[] = $message['code'];
                $errorMessages[] = $message['text'];
            }
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
