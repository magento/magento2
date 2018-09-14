<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Quote\Model\Quote as QuoteEntity;
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
     * Validates quote before submit.
     *
     * @param Quote $quote
     * @return $this
     * @throws LocalizedException
     */
    public function validateBeforeSubmit(QuoteEntity $quote)
    {
        if ($quote->getHasError()) {
            $errors = $this->getQuoteErrors($quote);
            throw new LocalizedException(__($errors ?: 'Something went wrong. Please try to place the order again.'));
        }

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

    /**
     * Parses quote error messages and concatenates them into single string.
     *
     * @param Quote $quote
     * @return string
     */
    private function getQuoteErrors(QuoteEntity $quote): string
    {
        $errors = array_map(
            function (Error $error) {
                return $error->getText();
            },
            $quote->getErrors()
        );

        return implode(PHP_EOL, $errors);
    }
}
