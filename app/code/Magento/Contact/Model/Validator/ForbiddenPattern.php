<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Contact\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Magento\Customer\Model\Validator\Pattern\ForbiddenValidator;
use Magento\Framework\DataObject;

/**
 * Validator for forbidden patterns in contact form fields.
 */
class ForbiddenPattern extends AbstractValidator
{
    /**
     * @var ForbiddenValidator
     */
    protected $forbiddenValidator;

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
     * Validate contact form data fields against forbidden patterns.
     *
     * @param DataObject $data
     * @return bool
     * @throws LocalizedException
     */
    public function isValid($data): bool
    {
        if (!$this->forbiddenValidator->isValidationEnabled()) {
            return true;
        }

        $dataFields = $data->getData();
        if (empty($dataFields)) {
            return true;
        }
        
        $isValid = $this->forbiddenValidator->validateDataRecursively($dataFields);
        
        if (!$isValid) {
            parent::_addMessages([
                __('Fraud Protection: Forbidden pattern detected in contact form data')
            ]);
        }

        return count($this->_messages) == 0;
    }
}
