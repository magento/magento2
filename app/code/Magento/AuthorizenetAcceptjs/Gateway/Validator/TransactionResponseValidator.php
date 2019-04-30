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
 * Validates the status of an attempted transaction
 */
class TransactionResponseValidator extends AbstractValidator
{
    const RESPONSE_CODE_APPROVED = 1;
    const RESPONSE_CODE_HELD = 4;
    const RESPONSE_REASON_CODE_APPROVED = 1;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;
    const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
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
        $response = $this->subjectReader->readResponse($validationSubject);
        $transactionResponse = $response['transactionResponse'];

        if ($this->isResponseCodeAnError($transactionResponse)) {
            $errorCodes = [];

            if (isset($transactionResponse['messages']['message']['code'])) {
                $errorCodes[] = $transactionResponse['messages']['message']['code'];
            } elseif (isset($transactionResponse['messages']['message'])) {
                foreach ($transactionResponse['messages']['message'] as $message) {
                    $errorCodes[] = $message['code'];
                }
            } elseif (isset($transactionResponse['errors'])) {
                foreach ($transactionResponse['errors'] as $message) {
                    $errorCodes[] = $message['errorCode'];
                }
            }

            return $this->createResult(false, $errorCodes);
        }

        return $this->createResult(true);
    }

    /**
     * Determines if the response code is actually an error
     *
     * @param array $transactionResponse
     * @return bool
     */
    private function isResponseCodeAnError(array $transactionResponse): bool
    {
        $code = $transactionResponse['messages']['message']['code']
            ?? $transactionResponse['messages']['message'][0]['code']
            ?? $transactionResponse['errors'][0]['errorCode']
            ?? null;
        $isErrorCode = !in_array(
            $transactionResponse['responseCode'],
            [self::RESPONSE_CODE_APPROVED, self::RESPONSE_CODE_HELD]
        );
        $responseReasonCodes = [
            self::RESPONSE_REASON_CODE_APPROVED,
            self::RESPONSE_REASON_CODE_PENDING_REVIEW,
            self::RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED,
        ];

        return $isErrorCode || (!$isErrorCode && $code && !in_array($code, $responseReasonCodes));
    }
}
