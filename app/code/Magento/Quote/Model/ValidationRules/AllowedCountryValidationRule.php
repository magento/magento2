<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Directory\Model\AllowedCountries;
use Magento\Quote\Model\Quote;

class AllowedCountryValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $defaultMessage;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @param AllowedCountries $allowedCountryReader
     * @param string $defaultMessage
     */
    public function __construct(AllowedCountries $allowedCountryReader, string $defaultMessage = '')
    {
        $this->defaultMessage = $defaultMessage;
        $this->allowedCountryReader = $allowedCountryReader;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];

        if (!$quote->isVirtual()) {
            $validationResult =
                in_array(
                    $quote->getShippingAddress()->getCountryId(),
                    $this->allowedCountryReader->getAllowedCountries()
                );
            if (!$validationResult) {
                $validationErrors = [$this->defaultMessage];
            }
        }

        return $validationErrors ? [get_class($this) => $validationErrors] : [];
    }
}
