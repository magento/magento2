<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;

/**
 * @inheritdoc
 */
class BillingAddressValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        string $generalMessage = ''
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setStoreId($quote->getStoreId());
        $validationResult = $billingAddress->validate();
        if (is_array($validationResult)) {
            $validationErrors = array_merge($validationErrors, $validationResult);
        }
        if ($quote->getCustomerId() === null && $quote->getCustomerId() !== $quote->getOrigData('customer_id')) {
            return [$this->validationResultFactory->create(['errors' => $validationErrors])];
        }
        if ($validationResult !== true) {
            $validationErrors = array_merge([__($this->generalMessage)], $validationErrors);
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
