<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResult;
use Magento\Quote\Model\Quote;

interface QuoteValidationRuleInterface
{
    /**
     * Validate quote model.
     *
     * @param Quote $quote
     * @return ValidationResult[]
     */
    public function validate(Quote $quote): array;
}