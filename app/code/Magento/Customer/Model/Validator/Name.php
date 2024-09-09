<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Customer\Model\Validator\Pattern\NameValidator;

/**
 * Customer name fields validator.
 */
class Name extends AbstractValidator
{
    /**
     * @var NameValidator
     */
    private NameValidator $nameValidator;

    /**
     * Constructor.
     *
     * @param NameValidator $nameValidator
     */
    public function __construct(NameValidator $nameValidator)
    {
        $this->nameValidator = $nameValidator;
    }

    /**
     * Validate name fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer): bool
    {        
        if (!$this->isValidName($customer->getFirstname())) {
            $firstname = __('Firstname');
            parent::_addMessages([__(
                '%1 is not valid! Allowed characters: %2',
                $firstname,
                $this->nameValidator->allowedCharsDescription
            )]);
        }

        if (!$this->isValidName($customer->getLastname())) {
            $lastname = __('Lastname');
            parent::_addMessages([__(
                '%1 is not valid! Allowed characters: %2',
                $lastname,
                $this->nameValidator->allowedCharsDescription
            )]);
        }

        if (!$this->isValidName($customer->getMiddlename())) {
            $middlename = __('Middlename');
            parent::_addMessages([__(
                '%1 is not valid! Allowed characters: %2',
                $middlename,
                $this->nameValidator->allowedCharsDescription
            )]);
        }

        return count($this->_messages) == 0;
    }

    /**
     * Check if name field is valid using the NameValidator.
     *
     * @param string|null $nameValue
     * @return bool
     */
    private function isValidName(?string $nameValue): bool
    {
        return $this->nameValidator->isValid($nameValue);
    }
}
