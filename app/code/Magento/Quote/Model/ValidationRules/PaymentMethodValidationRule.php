<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;

/**
 * @inheritdoc
 */
class PaymentMethodValidationRule implements QuoteValidationRuleInterface
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
        $validationResult = $quote->getPayment()->getMethod();
        if (!$validationResult) {
            $validationErrors = [__($this->generalMessage)];
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
