<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

/**
 * Similar to ValidatorComposite but also includes the aggregate error codes in the result
 */
class AggregateValidator extends AbstractValidator
{
    /**
     * @var ErrorCodeProvider
     */
    private $errorCodeProvider;

    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorCodeProvider $errorCodeProvider
     * @param ValidatorInterface[] $validators
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ErrorCodeProvider $errorCodeProvider,
        array $validators = []
    ) {
        parent::__construct($resultFactory);
        $this->errorCodeProvider = $errorCodeProvider;
        $this->validators = $validators;

        foreach ($this->validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'All validators must implement %s',
                    ValidatorInterface::class
                ));
            }
        }
    }

    /**
     * Aggregates the result of the validators in the chain
     *
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $errorMessages = [];

        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($validationSubject);

            if (!$validationResult->isValid()) {
                $isValid = ($isValid && $validationResult->isValid());
                $errorMessages = array_merge($errorMessages, $validationResult->getFailsDescription());
            }
        }

        $errorCodes = $this->errorCodeProvider->getErrorCodes($validationSubject);

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
