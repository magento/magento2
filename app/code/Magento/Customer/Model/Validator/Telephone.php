<?php
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Security\Model\Validator\Pattern\TelephoneValidator;

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
            if (!empty($fieldValue) && !$this->validateTelephoneField($fieldName, $fieldValue)) {
                parent::_addMessages([
                    __('%1 is not valid! Allowed characters: %2', $fieldName, $this->telephoneValidator->allowedCharsDescription)
                ]);
            }
        }

        return count($this->_messages) == 0;
    }

    /**
     * Validate a single telephone field.
     *
     * @param string $fieldName
     * @param mixed $telephoneValue
     * @return bool
     */
    private function validateTelephoneField(string $fieldName, mixed $telephoneValue): bool
    {
        return $this->telephoneValidator->isValid($telephoneValue);
    }
}
