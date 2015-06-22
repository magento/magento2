<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\ObjectManager\TMap;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class ValidatorComposite implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param TMap $validators
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        TMap $validators
    ) {
        $this->validators = $validators;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Performs domain level validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $failsDescriptionAggregate = [];
        foreach ($this->validators as $validator) {
            $result = $validator->validate($validationSubject);
            if (!$result->isValid()) {
                $isValid = false;
                $failsDescriptionAggregate = array_merge(
                    $failsDescriptionAggregate,
                    $result->getFailsDescription()
                );
            }
        }

        return $this->resultFactory->create(
            [
                'isValid' => $isValid,
                'failsDescription' => $failsDescriptionAggregate
            ]
        );
    }
}
