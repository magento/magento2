<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class ValidatorComposite
 * @package Magento\Payment\Gateway\Validator
 * @api
 * @since 2.0.0
 */
class ValidatorComposite extends AbstractValidator
{
    /**
     * @var ValidatorInterface[] | TMap
     * @since 2.0.0
     */
    private $validators;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param TMapFactory $tmapFactory
     * @param array $validators
     * @since 2.0.0
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        TMapFactory $tmapFactory,
        array $validators = []
    ) {
        $this->validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => ValidatorInterface::class
            ]
        );
        parent::__construct($resultFactory);
    }

    /**
     * Performs domain level validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     * @since 2.0.0
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

        return $this->createResult($isValid, $failsDescriptionAggregate);
    }
}
