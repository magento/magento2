<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Validator;

use Magento\Review\Model\Review;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Customer\Model\Validator\Pattern\ForbiddenValidator;

/**
 * Validator for forbidden patterns in multiple review fields.
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
     * Validate multiple review fields against forbidden patterns.
     *
     * @param array $values
     * @return bool
     */
    public function isValid($values): bool
    {
        if (!$this->forbiddenValidator->isValidationEnabled()) {
            return true;
        }

        foreach ($values as $field => $value) {
            if (empty($value)) {
                continue;
            }

            if (!$this->forbiddenValidator->isValid($value)) {
                parent::_addMessages([
                    __("Fraud Protection: Forbidden pattern detected in review field")
                ]);
            }
        }

        return count($this->_messages) == 0;
    }
}
