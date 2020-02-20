<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage;

/**
 * @inheritdoc
 */
class MinimumAmountValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var ValidationMessage
     */
    private $amountValidationMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationMessage $amountValidationMessage
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */
    public function __construct(
        ValidationMessage $amountValidationMessage,
        ValidationResultFactory $validationResultFactory,
        string $generalMessage = ''
    ) {
        $this->amountValidationMessage = $amountValidationMessage;
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
    }

    /**
     * @inheritdoc
     * @throws \Zend_Currency_Exception
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $validationResult = $quote->validateMinimumAmount($quote->getIsMultiShipping());
        if (!$validationResult) {
            if (!$this->generalMessage) {
                $this->generalMessage = $this->amountValidationMessage->getMessage();
            }
            $validationErrors = [__($this->generalMessage)];
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
