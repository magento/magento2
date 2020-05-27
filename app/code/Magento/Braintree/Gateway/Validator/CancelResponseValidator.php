<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Validator;

use Braintree\Error\ErrorCollection;
use Braintree\Error\Validation;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Braintree\Gateway\SubjectReader;

/**
 * Decorates the general response validator to handle specific cases.
 *
 * This validator decorates the general response validator to handle specific cases like
 * an expired or already voided on Braintree side authorization transaction.
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class CancelResponseValidator extends AbstractValidator
{
    /**
     * @var int
     */
    private static $acceptableTransactionCode = 91504;

    /**
     * @var GeneralResponseValidator
     */
    private $generalResponseValidator;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param GeneralResponseValidator $generalResponseValidator
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        GeneralResponseValidator $generalResponseValidator,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->generalResponseValidator = $generalResponseValidator;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $result = $this->generalResponseValidator->validate($validationSubject);
        if (!$result->isValid()) {
            $response = $this->subjectReader->readResponseObject($validationSubject);
            if ($this->isErrorAcceptable($response->errors)) {
                $result = $this->createResult(true, [__('Transaction is cancelled offline.')]);
            }
        }

        return $result;
    }

    /**
     * Checks if error collection has an acceptable error code.
     *
     * @param ErrorCollection $errorCollection
     * @return bool
     */
    private function isErrorAcceptable(ErrorCollection $errorCollection): bool
    {
        $errors = $errorCollection->deepAll();
        // there is should be only one acceptable error
        if (count($errors) > 1) {
            return false;
        }

        /** @var Validation $error */
        $error = array_pop($errors);

        return (int)$error->code === self::$acceptableTransactionCode;
    }
}
