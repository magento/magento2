<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Reservation\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * Extension point for base validation
 *
 * @api
 */
interface ReservationValidatorInterface
{
    /**
     * @param ReservationInterface $reservation
     * @return ValidationResult
     */
    public function validate(ReservationInterface $reservation): ValidationResult;
}
