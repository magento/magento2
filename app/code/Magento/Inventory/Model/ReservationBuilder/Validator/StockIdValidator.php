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
class StockIdValidator implements ReservationBuilderValidatorInterface
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

        $value = $reservationBuilder->getStockId();

        if (false === is_numeric($value)) { // TODO should also check if it is positive?
            $errors[] = __('"%field" is expected to be a number.', ['field' => 'stockId']);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
