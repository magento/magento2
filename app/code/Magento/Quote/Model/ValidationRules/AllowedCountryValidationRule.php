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
use Magento\Store\Model\ScopeInterface;

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
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setStoreId($quote->getStoreId());
            $validationResult =
                in_array(
                    $shippingAddress->getCountryId(),
                    $this->allowedCountryReader->getAllowedCountries(
                        ScopeInterface::SCOPE_STORE,
                        $quote->getStoreId()
                    )
                );
            if (!$validationResult) {
                $validationErrors = [__($this->generalMessage)];
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
