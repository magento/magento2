<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Validates that the request was successful
 */
class GeneralResponseValidator extends AbstractValidator
{
    /**
     * The result code that authorize.net returns for a successful Api call
     */
    private const RESULT_CODE_SUCCESS = 'Ok';

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
    public function validate(array $validationSubject): ResultInterface
    {
        $response = $this->subjectReader->readResponse($validationSubject);
        $isValid = (isset($response['messages']['resultCode'])
            && $response['messages']['resultCode'] === self::RESULT_CODE_SUCCESS);
        $errorCodes = [];
        $errorMessages = [];

        if (!$isValid) {
            if (isset($response['messages']['message']['code'])) {
                $errorCodes[] = $response['messages']['message']['code'];
                $errorMessages[] = $response['messages']['message']['text'];
            } elseif (isset($response['messages']['message'])) {
                foreach ($response['messages']['message'] as $message) {
                    $errorCodes[] = $message['code'];
                    $errorMessages[] = $message['text'];
                }
            } elseif (isset($response['errors']['error'])) {
                foreach ($response['errors']['error'] as $message) {
                    $errorCodes[] = $message['errorCode'];
                    $errorMessages[] = $message['errorText'];
                }
            }
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
