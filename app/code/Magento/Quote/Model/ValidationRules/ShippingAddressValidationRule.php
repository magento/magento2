<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Quote\Model\Quote;

class ShippingAddressValidationRule implements QuoteValidationRuleInterface
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
            $validationResult = $quote->getShippingAddress()->validate();
            if ($validationResult !== true) {
                $validationErrors = [$this->defaultMessage];
            }
            if (is_array($validationResult)) {
                $validationErrors = array_merge($validationErrors, $validationResult);
            }
        }

        return $validationErrors ? [get_class($this) => $validationErrors] : [];
    }
}
