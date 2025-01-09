<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
            parent::_addMessages([['firstname' => 'First Name is not valid!']]);
        }

        if (!$this->isValidName($customer->getLastname())) {
            parent::_addMessages([['lastname' => 'Last Name is not valid!']]);
        }

        if (!$this->isValidName($customer->getMiddlename())) {
            parent::_addMessages([['middlename' => 'Middle Name is not valid!']]);
        }
        
        return count($this->_messages) == 0;
    }

    /**
     * Check if name field is valid using the NameValidator.
     *
     * @param string|null $nameValue
     * @return bool
     */
    private function isValidName($nameValue): bool
    {
        return $this->nameValidator->isValid($nameValue);
    }
}
