<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class ValidatorComposite implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[] | TMap
     */
    private $validators;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param array $validators
     * @param TMapFactory $tmapFactory
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        array $validators,
        TMapFactory $tmapFactory
    ) {
        $this->validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => 'Magento\Payment\Gateway\Validator\ValidatorInterface'
            ]
        );
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
