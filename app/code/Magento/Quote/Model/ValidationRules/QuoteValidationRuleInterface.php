<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\ValidationRules;

use Magento\Quote\Model\Quote;

interface QuoteValidationRuleInterface
{
    /**
     * Validate quote model.
     *
     * @param Quote $quote
     * @return array
     * [
     *      'ruleId_1' => [
     *          'Base error message',
     *          'Additional error message #1',
     *          'Additional error message #2',
     *          'Additional error message #3',
     *          'Additional error message #4',
     *      ],
     *      'ruleId_2' => [
     *          'Base error message',
     *      ]
     * ]
     */
    public function validate(Quote $quote): array;
}