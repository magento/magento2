<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage;

class MinimumAmountValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $defaultMessage;

    /**
     * @var ValidationMessage
     */
    private $amountValidationMessage;

    /**
     * @param ValidationMessage $amountValidationMessage
     * @param string $defaultMessage
     */
    public function __construct(ValidationMessage $amountValidationMessage, string $defaultMessage = '')
    {
        $this->amountValidationMessage = $amountValidationMessage;
        $this->defaultMessage = $defaultMessage;
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
            if (!$this->defaultMessage) {
                $this->defaultMessage = $this->amountValidationMessage->getMessage();
            }
            $validationErrors = [$this->defaultMessage];
        }

        return $validationErrors ? [get_class($this) => $validationErrors] : [];
    }
}
