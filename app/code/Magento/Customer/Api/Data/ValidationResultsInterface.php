<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Validation results interface.
 * @api
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
     * Set if the provided data is valid.
     *
     * @param bool $isValid
     * @return $this
     */
    public function setIsValid($isValid);

    /**
     * Get error messages as array in case of validation failure, else return empty array.
     *
     * @return string[]
     */
    public function getMessages();

    /**
     * Set error messages as array in case of validation failure.
     *
     * @param string[] $messages
     * @return string[]
     */
    public function setMessages(array $messages);
}
