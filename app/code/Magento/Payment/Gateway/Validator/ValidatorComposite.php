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
 * Compiles a result using the results of multiple validators
 *
 * @api
 * @since 100.0.2
 */
class ValidatorComposite extends AbstractValidator
{
    /**
     * @var ValidatorInterface[] | TMap
     */
    private $validators;

    /**
     * @var array
     */
    private $chainBreakingValidators;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param TMapFactory $tmapFactory
     * @param array $validators
     * @param array $chainBreakingValidators
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        TMapFactory $tmapFactory,
        array $validators = [],
        array $chainBreakingValidators = []
    ) {
        $this->validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => ValidatorInterface::class
            ]
        );
        $this->chainBreakingValidators = $chainBreakingValidators;
        parent::__construct($resultFactory);
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
        $failsDescriptionAggregate = [[]];
        $errorCodesAggregate = [[]];
        foreach ($this->validators as $key => $validator) {
            $result = $validator->validate($validationSubject);
            if (!$result->isValid()) {
                $isValid = false;
                $failsDescriptionAggregate[] = $result->getFailsDescription();
                $errorCodesAggregate[] = $result->getErrorCodes();

                if (!empty($this->chainBreakingValidators[$key])) {
                    break;
                }
            }
        }

        return $this->createResult(
            $isValid,
            array_merge(...$failsDescriptionAggregate),
            array_merge(...$errorCodesAggregate)
        );
    }
}
