<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Customer\Model\Validator\Pattern\StreetValidator;

/**
 * Customer street fields validator.
 */
class Street extends AbstractValidator
{
    /**
     * @var StreetValidator
     */
    private StreetValidator $streetValidator;

    /**
     * Constructor.
     *
     * @param StreetValidator $streetValidator
     */
    public function __construct(StreetValidator $streetValidator)
    {
        $this->streetValidator = $streetValidator;
    }

    /**
     * Validate street field.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer): bool
    {
        if (!$this->streetValidator->isValidationEnabled()) {
            return true;
        }

        $streets = $customer->getStreet();
        if (empty($streets)) {
            return true;
        }

        foreach ($streets as $street) {
            if (!$this->validateStreetField($street)) {
                parent::_addMessages(
                    [
                        'street' => __(
                            'Street is not valid! Allowed characters: %1',
                            $this->streetValidator->allowedCharsDescription
                        ),
                    ]
                );
            }
        }

        return count($this->_messages) == 0;
    }

    /**
     * Validate the street field.
     *
     * @param string|null $streetValue
     * @return bool
     */
    private function validateStreetField(?string $streetValue): bool
    {
        return $this->streetValidator->isValid($streetValue);
    }
}
