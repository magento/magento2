<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Compiles the of multiple validators with the option to break the chain when one fails
 */
class ValidatorChain extends AbstractValidator
{
    /**
     * @var ValidatorInterface[]
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
        parent::__construct($resultFactory);
        $this->chainBreakingValidators = $chainBreakingValidators;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $failsDescriptionAggregate = [];
        $errorCodesAggregate = [];
        foreach ($this->validators as $key => $validator) {
            $result = $validator->validate($validationSubject);
            if ($result->isValid()) {
                continue;
            }

            $isValid = false;
            $failsDescriptionAggregate = array_merge(
                $failsDescriptionAggregate,
                $result->getFailsDescription()
            );
            $errorCodesAggregate = array_merge(
                $errorCodesAggregate,
                $result->getErrorCodes()
            );

            if (!empty($this->chainBreakingValidators[$key])) {
                break;
            }
        }

        return $this->createResult($isValid, $failsDescriptionAggregate, $errorCodesAggregate);
    }
}
