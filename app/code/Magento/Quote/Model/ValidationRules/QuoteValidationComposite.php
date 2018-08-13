<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Quote\Model\Quote;

class QuoteValidationComposite implements QuoteValidationRuleInterface
{
    /**
     * @var QuoteValidationRuleInterface[]
     */
    private $validationRules = [];

    public function __construct(array $validationRules)
    {
        $this->validationRules = $validationRules;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $aggregateResult = [];

        foreach ($this->validationRules as $validationRule) {
            $ruleValidationResult = $validationRule->validate($quote);
            $aggregateResult += $ruleValidationResult;
        }

        return $aggregateResult;
    }
}
