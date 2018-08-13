<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Quote\Model\Quote;

class PaymentMethodValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $defaultMessage;

    /**
     * @param string $defaultMessage
     */
    public function __construct(string $defaultMessage = '')
    {
        $this->defaultMessage = $defaultMessage;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $validationResult = $quote->getPayment()->getMethod();
        if (!$validationResult) {
            $validationErrors = [$this->defaultMessage];
        }

        return $validationErrors ? [get_class($this) => $validationErrors] : [];
    }
}
