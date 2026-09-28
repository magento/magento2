<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Config\Password;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\UserValidationRules;

/**
 * Backend model for admin minimum password length configuration
 */
class MinimumLength extends Value
{
    /**
     * Validate the minimum password length value
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = (int) $this->getValue();

        if ($value < UserValidationRules::MIN_PASSWORD_LENGTH) {
            throw new LocalizedException(
                __(
                    'The minimum admin password length must be at least %1 characters.',
                    UserValidationRules::MIN_PASSWORD_LENGTH
                )
            );
        }

        return parent::beforeSave();
    }
}
