<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Reservation\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * Check that stock ID is valid
 */
class QuantityValidator implements ReservationValidatorInterface
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
    public function validate(ReservationInterface $reservation): ValidationResult
    {
        $errors = [];
        $value = $reservation->getQuantity();

        if (null === $value) {
            $errors[] = __('"%field" can not be null.', ['field' => ReservationInterface::QUANTITY]);
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
