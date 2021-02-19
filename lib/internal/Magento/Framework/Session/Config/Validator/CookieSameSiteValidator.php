<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Config\Validator;

/**
 * Cookie SameSite Attribute validator
 */
class CookieSameSiteValidator extends \Magento\Framework\Validator\AbstractValidator
{
    /**#@+
     * Constant for validating same site allowed values
     */
    private const SAME_SITE_ALLOWED_VALUES = [
        'strict',
        'lax',
        'none'
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        return in_array(strtolower($value), self::SAME_SITE_ALLOWED_VALUES);
    }
}
