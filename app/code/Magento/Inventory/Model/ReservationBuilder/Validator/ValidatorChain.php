<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ReservationBuilder\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\ReservationBuilderInterface;

/**
 * Chain of validators. Extension point for new validators via di configuration
 */
class ValidatorChain implements ReservationBuilderValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ReservationBuilderValidatorInterface[]
     */
    private $validators;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param ReservationBuilderValidatorInterface[] $validators
     * @throws LocalizedException
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $validators = []
    ) {
        $this->validationResultFactory = $validationResultFactory;

        foreach ($validators as $validator) {
            if (!$validator instanceof ReservationBuilderValidatorInterface) {
                throw new LocalizedException(
                    __('ReservationBuilder Validator must implement ReservationBuilderValidatorInterface.')
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(ReservationBuilderInterface $reservationBuilder): ValidationResult
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($reservationBuilder);

            if (!$validationResult->isValid()) {
                $errors = array_merge($errors, $validationResult->getErrors());
            }
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
