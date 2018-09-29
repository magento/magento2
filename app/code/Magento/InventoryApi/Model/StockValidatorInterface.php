<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Model;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Responsible for Stock validation
 * Extension point for base validation
 *
 * @api
 */
interface StockValidatorInterface
{
    /**
     * @param StockInterface $stock
     * @return ValidationResult
     */
    public function validate(StockInterface $stock): ValidationResult;
}
