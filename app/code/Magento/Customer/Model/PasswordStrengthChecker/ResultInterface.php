<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\PasswordStrengthChecker;

use Magento\Framework\Phrase;

/**
 * Interface ResultInterface
 * @package Magento\Customer\Model\PasswordStrengthChecker
 */
interface ResultInterface
{
    /**
     * Get is valid.
     *
     * @return bool
     */
    public function getIsValid(): bool;

    /**
     * Set is valid.
     *
     * @param bool $valid
     * @return void
     */
    public function setIsValid(bool $valid);

    /**
     * Get message.
     *
     * @return Phrase
     */
    public function getMessage(): Phrase;

    /**
     * Set message.
     *
     * @param Phrase $message
     * @return void
     */
    public function setMessage(Phrase $message);
}
