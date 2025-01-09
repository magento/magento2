<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Magento\Customer\Model\Validator\Pattern\EmailAddressValidator;

/**
 * Email fields validator for newsletter subscription.
 */
class Email extends AbstractValidator
{
    /**
     * @var EmailAddressValidator
     */
    private EmailAddressValidator $emailValidator;

    /**
     * Constructor.
     *
     * @param EmailAddressValidator $emailValidator
     */
    public function __construct(
        EmailAddressValidator $emailValidator
    ) {
        $this->emailValidator = $emailValidator;
    }

    /**
     * Validate email fields.
     *
     * @param string $email
     * @return bool
     */
    public function isValid($email): bool
    {
        if (!$this->emailValidator->isValidationEnabled()) {
            return true;
        }

        if (!$this->validateEmailField($email)) {
            return false;
        }

        return count($this->_messages) == 0;
    }

    /**
     * Validate the email field.
     *
     * @param string|null $emailValue
     * @return bool
     */
    private function validateEmailField(?string $emailValue): bool
    {
        if (!$this->emailValidator->isValid($emailValue)) {
            parent::_addMessages(
                [
                    __(
                        'Email address is not valid! Allowed characters: %1',
                        $this->emailValidator->allowedCharsDescription
                    ),
                ]
            );
            return false;
        }

        if ($this->isBlacklistEmail($emailValue)) {
            parent::_addMessages([
                __('The email address or domain is blacklisted.')
            ]);
            return false;
        }

        return true;
    }

    /**
     * Check if email field is blacklisted using the EmailAddressValidator.
     *
     * @param string|null $emailValue
     * @return bool
     */
    private function isBlacklistEmail(?string $emailValue): bool
    {
        return $this->emailValidator->isBlacklist($emailValue);
    }
}
