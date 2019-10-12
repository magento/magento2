<?php
/**
 * Protocol validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Encryption;

use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Encryption Key Validator
 */
class KeyValidator
{
    /**
     * Validate encryption key
     *
     * Validate that encryption key is exactly 32 characters long and has
     * no trailing spaces, no invisible characters (tabs, new lines, etc.)
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value) : bool
    {
        return strlen($value) === ConfigOptionsListConstants::STORE_KEY_RANDOM_STRING_SIZE
            && preg_match('/^\S+$/', $value);
    }
}
