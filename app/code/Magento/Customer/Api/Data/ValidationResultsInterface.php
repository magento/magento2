<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Validation results interface.
 */
interface ValidationResultsInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const VALID = 'valid';
    const MESSAGES = 'messages';
    /**#@-*/

    /**
     * Check if the provided data is valid.
     *
     * @return bool
     */
    public function isValid();

    /**
     * Get error messages as array in case of validation failure, else return empty array.
     *
     * @return string[]
     */
    public function getMessages();
}
