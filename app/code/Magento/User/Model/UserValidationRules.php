<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model;

use Laminas\Validator\Identical;
use Magento\Framework\Validator\DataObject;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\StringLength;

/**
 * Class for adding validation rules to an Admin user
 *
 * @api
 * @since 100.0.2
 */
class UserValidationRules
{
    /**
     * Minimum length of admin password
     */
    public const MIN_PASSWORD_LENGTH = 7;

    /**
     * Adds validation rule for user first name, last name, username and email
     *
     * @param DataObject $validator
     * @return DataObject
     */
    public function addUserInfoRules(DataObject $validator)
    {
        $userNameNotEmpty = new NotEmpty();
        $userNameNotEmpty->setMessage(
            __('"User Name" is required. Enter and try again.'),
            NotEmpty::IS_EMPTY
        );
        $firstNameNotEmpty = new NotEmpty();
        $firstNameNotEmpty->setMessage(
            __('"First Name" is required. Enter and try again.'),
            NotEmpty::IS_EMPTY
        );
        $lastNameNotEmpty = new NotEmpty();
        $lastNameNotEmpty->setMessage(
            __('"Last Name" is required. Enter and try again.'),
            NotEmpty::IS_EMPTY
        );
        $emailValidity = new EmailAddress();
        $emailValidity->setMessage(
            __('Please enter a valid email.'),
            EmailAddress::INVALID
        );

        /** @var $validator DataObject */
        $validator->addRule(
            $userNameNotEmpty,
            'username'
        )->addRule(
            $firstNameNotEmpty,
            'firstname'
        )->addRule(
            $lastNameNotEmpty,
            'lastname'
        )->addRule(
            $emailValidity,
            'email'
        );

        return $validator;
    }

    /**
     * Adds validation rule for user password
     *
     * @param DataObject $validator
     * @return DataObject
     */
    public function addPasswordRules(DataObject $validator)
    {
        $passwordNotEmpty = new NotEmpty();
        $passwordNotEmpty->setMessage(__('Password is required field.'), NotEmpty::IS_EMPTY);
        $minPassLength = self::MIN_PASSWORD_LENGTH;
        $passwordLength = new StringLength(['min' => $minPassLength, 'encoding' => 'UTF-8']);
        $passwordLength->setMessage(
            __('Your password must be at least %1 characters.', $minPassLength),
            StringLength::TOO_SHORT
        );
        $passwordChars = new Regex('/[a-z].*\d|\d.*[a-z]/iu');
        $passwordChars->setMessage(
            __('Your password must include both numeric and alphabetic characters.'),
            Regex::NOT_MATCH
        );
        $validator->addRule(
            $passwordNotEmpty,
            'password'
        )->addRule(
            $passwordLength,
            'password'
        )->addRule(
            $passwordChars,
            'password'
        );

        return $validator;
    }

    /**
     * Adds validation rule for user password confirmation
     *
     * @param DataObject $validator
     * @param string $passwordConfirmation
     * @return DataObject
     */
    public function addPasswordConfirmationRule(
        DataObject $validator,
        $passwordConfirmation
    ) {
        $passwordConfirmation = new Identical($passwordConfirmation);
        $passwordConfirmation->setMessage(
            __('Your password confirmation must match your password.'),
            Identical::NOT_SAME
        );
        $validator->addRule($passwordConfirmation, 'password');
        return $validator;
    }
}
