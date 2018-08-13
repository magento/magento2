<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Quote\Model\Quote;

class ShippingMethodValidationRule implements QuoteValidationRuleInterface
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

        if (!$quote->isVirtual()) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            $shippingRate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
            $validationResult = $shippingMethod && $shippingRate;
            if (!$validationResult) {
                $validationErrors = [$this->defaultMessage];
            }
        }

        return $validationErrors ? [get_class($this) => $validationErrors] : [];
    }
}
