<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage as OrderAmountValidationMessage;
use Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;

/**
 * @api
 * @since 100.0.2
 */
class QuoteValidator
{
    /**
     * Maximum available number
     */
    const MAXIMUM_AVAILABLE_NUMBER = 99999999;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @var OrderAmountValidationMessage
     */
    private $minimumAmountMessage;

    /**
     * @var QuoteValidationRuleInterface
     */
    private $quoteValidationRule;

    /**
     * QuoteValidator constructor.
     *
     * @param AllowedCountries|null $allowedCountryReader
     * @param OrderAmountValidationMessage|null $minimumAmountMessage
     * @param QuoteValidationRuleInterface|null $quoteValidationRule
     */
    public function __construct(
        AllowedCountries $allowedCountryReader = null,
        OrderAmountValidationMessage $minimumAmountMessage = null,
        QuoteValidationRuleInterface $quoteValidationRule = null
    ) {
        $this->allowedCountryReader = $allowedCountryReader ?: ObjectManager::getInstance()
            ->get(AllowedCountries::class);
        $this->minimumAmountMessage = $minimumAmountMessage ?: ObjectManager::getInstance()
            ->get(OrderAmountValidationMessage::class);
        $this->quoteValidationRule = $quoteValidationRule ?: ObjectManager::getInstance()
            ->get(QuoteValidationRuleInterface::class);
    }

    /**
     * Validate quote amount
     *
     * @param QuoteEntity $quote
     * @param float $amount
     * @return $this
     */
    public function validateQuoteAmount(QuoteEntity $quote, $amount)
    {
        if (!$quote->getHasError() && $amount >= self::MAXIMUM_AVAILABLE_NUMBER) {
            $quote->setHasError(true);
            $quote->addMessage(__('This item price or quantity is not valid for checkout.'));
        }
        return $this;
    }

    /**
     * Validate quote before submit
     *
     * @param Quote $quote
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateBeforeSubmit(QuoteEntity $quote)
    {
        foreach ($this->quoteValidationRule->validate($quote) as $validationResult) {
            if ($validationResult->isValid()) {
                continue;
            }

            $messages = $validationResult->getErrors();
            $defaultMessage = array_shift($messages);
            if ($defaultMessage && !empty($messages)) {
                $defaultMessage .= ' %1';
            }
            if ($defaultMessage) {
                throw new LocalizedException(__($defaultMessage, implode(' ', $messages)));
            }
        }

        return $this;
    }
}
