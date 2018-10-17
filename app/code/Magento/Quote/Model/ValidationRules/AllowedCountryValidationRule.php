<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;

/**
 * @inheritdoc
 */
class AllowedCountryValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param AllowedCountries $allowedCountryReader
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */
    public function __construct(
        AllowedCountries $allowedCountryReader,
        ValidationResultFactory $validationResultFactory,
        string $generalMessage = ''
    ) {
        $this->allowedCountryReader = $allowedCountryReader;
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
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
                $validationErrors = [__($this->generalMessage)];
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
