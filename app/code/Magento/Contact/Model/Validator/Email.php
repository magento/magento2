<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Contact\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Magento\Customer\Model\Validator\Pattern\EmailAddressValidator;
use Magento\Framework\DataObject;

/**
 * Validator for email fields in contact form.
 */
class Email extends AbstractValidator
{
    /**
     * @var EmailAddressValidator
     */
    protected $emailValidator;

    /**
     * Constructor.
     *
     * @param EmailAddressValidator $emailValidator
     */
    public function __construct(EmailAddressValidator $emailValidator)
    {
        $this->emailValidator = $emailValidator;
    }

    /**
     * Validate email fields.
     *
     * @param DataObject $data
     * @return bool
     */
    public function isValid($data): bool
    {
        if (!$this->emailValidator->isValidationEnabled()) {
            return true;
        }

        $email = $data->getData('email');
        if (empty($email)) {
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
    protected function validateEmailField(?string $emailValue): bool
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
    protected function isBlacklistEmail(?string $emailValue): bool
    {
        return $this->emailValidator->isBlacklist($emailValue);
    }
}
