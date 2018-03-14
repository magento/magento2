<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\PasswordStrengthChecker;

use Magento\Framework\Phrase;

/**
 * Class Result
 * @package Magento\Customer\Model\PasswordStrengthChecker
 */
class Result implements ResultInterface
{
    /**
     * @var bool
     */
    private $valid;

    /**
     * @var Phrase
     */
    private $message;

    /**
     * Result constructor.
     * @param bool $valid
     * @param Phrase $message
     */
    public function __construct(bool $valid, Phrase $message)
    {
        $this->valid = $valid;
        $this->message = $message;
    }

    /**
     * Is result valid.
     *
     * @return bool
     */
    public function getIsValid(): bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     * @return void
     */
    public function setIsValid(bool $valid)
    {
        $this->valid = $valid;
    }

    /**
     * Get the message.
     *
     * @return Phrase
     */
    public function getMessage(): Phrase
    {
        return $this->message;
    }

    /**
     * @param Phrase $message
     * @return void
     */
    public function setMessage(Phrase $message)
    {
        $this->message = $message;
    }
}
