<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ReservationBuilder\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Inventory\Model\ReservationBuilderInterface;

/**
 * Extension point for base validation
 *
 * @api
 */
interface ReservationBuilderValidatorInterface
{
    /**
     * @param ReservationBuilderInterface $reservationBuilder
     * @return ValidationResult
     */
    public function validate(ReservationBuilderInterface $reservationBuilder): ValidationResult;
}
