<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventoryApi\Model;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * Responsible for Stock Source link validation
 * Extension point for base validation
 *
 * @api
 */
interface StockSourceLinkValidatorInterface
{
    /**
     * @param StockSourceLinkInterface $link
     * @return ValidationResult
     */
    public function validate(StockSourceLinkInterface $link): ValidationResult;
}
