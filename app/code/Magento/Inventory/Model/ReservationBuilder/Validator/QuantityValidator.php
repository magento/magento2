<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ReservationBuilder\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Inventory\Model\ReservationBuilderInterface;

/**
 * Check that stock ID is valid
 */
class QuantityValidator implements ReservationBuilderValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    public function validate(ReservationBuilderInterface $reservationBuilder): ValidationResult
    {
        $errors = [];

        $value = $reservationBuilder->getQuantity();

        if (false === is_numeric($value)) {
            $errors[] = __('"%field" is expected to be a number.', ['field' => 'quantity']);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
