<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Stock\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * TODO: more clear description
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
    public function validate(StockInterface $stock);
}
