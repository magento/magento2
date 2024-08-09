<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Framework\Validator\GlobalNameValidator;

/**
 * Class NameValidationRule
 * Validates the first name and last name fields in a quote.
 */
class NameValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * Constructor.
     *
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * Validate the first name, middle name, and last name in the quote.
     *
     * @param Quote $quote
     * @return array
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $firstName = $quote->getCustomerFirstname();
        $middleName = $quote->getCustomerMiddlename();
        $lastName = $quote->getCustomerLastname();

        if (!GlobalNameValidator::isValidName($firstName)) {
            $validationErrors[] = __('First Name is not valid');
        }

        if (!GlobalNameValidator::isValidName($middleName)) {
            $validationErrors[] = __('Middle Name is not valid');
        }

        if (!GlobalNameValidator::isValidName($lastName)) {
            $validationErrors[] = __('Last Name is not valid');
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
