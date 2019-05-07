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
     * @var string
     */
    private static $resultCodeSuccess = 'Ok';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);
        $isValid = (isset($response['messages']['resultCode'])
            && $response['messages']['resultCode'] === self::$resultCodeSuccess);
        $errorCodes = [];

        if (!$isValid) {
            if (isset($response['messages']['message']['code'])) {
                $errorCodes[] = $response['messages']['message']['code'];
            } elseif (isset($response['messages']['message'])) {
                foreach ($response['messages']['message'] as $message) {
                    $errorCodes[] = $message['code'];
                }
            } elseif (isset($response['errors']['error'])) {
                foreach ($response['errors']['error'] as $message) {
                    $errorCodes[] = $message['errorCode'];
                }
            }
        }

        return $this->createResult($isValid, $errorCodes);
    }
}
