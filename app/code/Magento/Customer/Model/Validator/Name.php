<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Security\Model\Validator\Pattern\NameValidator;

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

        $nameFields = [
            'Firstname' => $customer->getFirstname(),
            'Lastname' => $customer->getLastname(),
            'Middlename' => $customer->getMiddlename()
        ];

        foreach ($nameFields as $fieldName => $fieldValue) {
            if (!empty($fieldValue) && !$this->isValidName($fieldValue)) {
                parent::_addMessages([
                    __('%1 is not valid! Allowed characters: %2',
                       $fieldName,
                       $this->nameValidator->allowedCharsDescription)
                ]);
            }
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
