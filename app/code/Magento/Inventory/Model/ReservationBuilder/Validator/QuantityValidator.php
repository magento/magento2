<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ReservationBuilder\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\ReservationBuilderInterface;

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

    /**
     * @inheritdoc
     */
    public function validate(ReservationBuilderInterface $reservationBuilder): ValidationResult
    {
        $errors = [];

        $value = $reservationBuilder->getQuantity();

        if (null === $value) {
            $errors[] = __('"%field" can not be null.', ['field' => 'quantity']);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
