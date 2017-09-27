<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Reservation\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * Check that stock ID is valid
 */
class StockIdValidator implements ReservationValidatorInterface
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
        $value = $reservation->getStockId();

        if (false === is_numeric($value)) {
            $errors[] = new Phrase('"%field" is expected to be a number.', ['field' => ReservationInterface::STOCK_ID]);
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
