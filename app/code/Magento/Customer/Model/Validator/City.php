<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Security\Model\Validator\Pattern\CityValidator;

/**
 * Customer city fields validator.
 */
class City extends AbstractValidator
{
    /**
     * @var CityValidator
     */
    private CityValidator $cityValidator;

    /**
     * Constructor.
     *
     * @param CityValidator $cityValidator
     */
    public function __construct(CityValidator $cityValidator)
    {
        $this->cityValidator = $cityValidator;
    }

    /**
     * Validate city field.
     *
     * @param Customer $entity
     * @return bool
     */
    public function isValid($entity): bool
    {
        if (!$this->cityValidator->isValidationEnabled()) {
            return true;
        }

        $cityField = $entity->getCity();
        if (empty($cityField)) {
            return true;
        }

        if (!$this->validateCityField('City', $cityField)) {
            parent::_addMessages([
                __('%1 is not valid! Allowed characters: %2', 'City', $this->cityValidator->allowedCharsDescription)
            ]);
        }

        return count($this->_messages) == 0;
    }

    /**
     * Validate the city field.
     *
     * @param string $fieldName
     * @param string|null $cityValue
     * @return bool
     */
    private function validateCityField(string $fieldName, ?string $cityValue): bool
    {
        return $this->cityValidator->isValid($cityValue);
    }
}
