<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Customer\Model\Validator\Pattern\ForbiddenValidator;

/**
 * Validator for forbidden patterns in customer EAV data.
 */
class ForbiddenPattern extends AbstractValidator
{
    /**
     * @var ForbiddenValidator
     */
    private ForbiddenValidator $forbiddenValidator;

    /**
     * Constructor.
     *
     * @param ForbiddenValidator $forbiddenValidator
     */
    public function __construct(ForbiddenValidator $forbiddenValidator)
    {
        $this->forbiddenValidator = $forbiddenValidator;
    }

    /**
     * Validate EAV data fields against forbidden patterns.
     *
     * @param mixed $customer
     * @return bool
     * @throws LocalizedException
     */
    public function isValid($customer): bool
    {
        if (!$this->forbiddenValidator->isValidationEnabled()) {
            return true;
        }

        $customerData = $customer->getData();
        if (empty($customerData)) {
            return true;
        }
        
        $isValid = $this->forbiddenValidator->validateDataRecursively($customerData);
        
        if (!$isValid) {
            parent::_addMessages([
                __('Fraud Protection: Forbidden pattern detected in customer data')
            ]);
        }

        return count($this->_messages) == 0;
    }
}
