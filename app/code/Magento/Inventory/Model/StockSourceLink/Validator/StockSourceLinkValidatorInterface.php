<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Inventory\Model\StockSourceLink;

/**
 * Responsible for StockSourceLink validation
 * Extension point for base validation
 *
 * @api
 */
interface StockSourceLinkValidatorInterface
{
    /**
     * @param StockSourceLink[] $links
     * @return ValidationResult
     */
    public function validate(array $links): ValidationResult;
}
