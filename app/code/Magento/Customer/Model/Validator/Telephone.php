<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Customer\Model\Validator\Pattern\TelephoneValidator;

/**
 * Customer telephone fields validator.
 */
class Telephone extends AbstractValidator
{
    /**
     * @var TelephoneValidator
     */
    private TelephoneValidator $telephoneValidator;

    /**
     * Constructor.
     *
     * @param TelephoneValidator $telephoneValidator
     */
    public function __construct(TelephoneValidator $telephoneValidator)
    {
        $this->telephoneValidator = $telephoneValidator;
    }

    /**
     * Validate telephone fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer): bool
    {
        if (!$this->telephoneValidator->isValidationEnabled()) {
            return true;
        }

        $telephoneFields = [
            'Phone Number' => $customer->getTelephone(),
            'Fax Number' => $customer->getFax()
        ];

        foreach ($telephoneFields as $fieldName => $fieldValue) {
            if (!empty($fieldValue) && !$this->validateTelephoneField($fieldValue)) {
                parent::_addMessages(
                    [
                        __(
                            '%1 is not valid! Allowed characters: %2',
                            $fieldName,
                            $this->telephoneValidator->allowedCharsDescription
                        ),
                    ]
                );
            }
        }

        return count($this->_messages) == 0;
    }

    /**
     * Validate a single telephone field.
     *
     * @param int|string|null $telephoneValue
     * @return bool
     */
    private function validateTelephoneField(int|string|null $telephoneValue): bool
    {
        return $this->telephoneValidator->isValid($telephoneValue);
    }
}
