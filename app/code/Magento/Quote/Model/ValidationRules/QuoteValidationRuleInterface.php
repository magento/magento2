<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResult;
use Magento\Quote\Model\Quote;

/**
 * Provides validation of Quote model.
 *
 * @api
 */
interface QuoteValidationRuleInterface
{
    /**
     * Validate Quote model.
     *
     * @param Quote $quote
     * @return ValidationResult[]
     */
    public function validate(Quote $quote): array;
}
