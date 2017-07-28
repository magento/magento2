<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Validation results interface.
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isValid();

    /**
     * Set if the provided data is valid.
     *
     * @param bool $isValid
     * @return $this
     * @since 2.0.0
     */
    public function setIsValid($isValid);

    /**
     * Get error messages as array in case of validation failure, else return empty array.
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getMessages();

    /**
     * Set error messages as array in case of validation failure.
     *
     * @param string[] $messages
     * @return string[]
     * @since 2.0.0
     */
    public function setMessages(array $messages);
}
