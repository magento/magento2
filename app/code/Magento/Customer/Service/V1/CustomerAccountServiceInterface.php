<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Service\V1;

/**
 * Manipulate Customer Address Entities *
 */
interface CustomerAccountServiceInterface
{
    /** account response status @deprecated */
    const ACCOUNT_CONFIRMATION = "confirmation";
    const ACCOUNT_REGISTERED = "registered";

    // Constants for the type of new account email to be sent
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';     // welcome email, when confirmation is disabled
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';       // welcome email, when confirmation is enabled
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation'; // email with confirmation link

    // Constants for confirmation statuses
    const ACCOUNT_CONFIRMED = 'account_confirmed';
    const ACCOUNT_CONFIRMATION_REQUIRED = 'account_confirmation_required';
    const ACCOUNT_CONFIRMATION_NOT_REQUIRED = 'account_confirmation_not_required';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    const EMAIL_REMINDER = 'email_reminder';
    const EMAIL_RESET = 'email_reset';

    /**
     * Create Customer Account
     *
     * @param Dto\Customer $customer
     * @param Dto\Address[] $addresses
     * @param string $password If null then a random password will be assigned
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the customer to a product they were looking at after pressing confirmation link.
     * @return Dto\Customer
     * @throws \Exception If something goes wrong during save
     * @throws \Magento\Exception\InputException If bad input is provided
     * @throws \Magento\Exception\StateException If the provided email is already used
     */
    public function createAccount(Dto\Customer $customer, array $addresses, $password = null, $redirectUrl = '');

    /**
     * Update Customer Account
     *
     * @param Dto\Customer $customer
     * @param Dto\Address[]|null $addresses Full array of addresses to associate with customer,
     *                                      or null if no change to addresses
     * @return void
     */
    public function updateAccount(Dto\Customer $customer, array $addresses = null);

    /**
     * Used to activate a customer account using a key that was sent in a confirmation e-mail.
     *
     * @param int $customerId
     * @return Dto\Customer
     * @throws \Magento\Exception\NoSuchEntityException If customer doesn't exist
     * @throws \Magento\Exception\StateException
     *      StateException::INVALID_STATE_CHANGE if account already active.
     */
    public function activateAccount($customerId);

    /**
     * Validate an account confirmation key matches expected value for customer
     *
     * @param int $customerId
     * @param string $confirmationKey
     * @return true if customer is valid
     * @throws \Magento\Exception\NoSuchEntityException If customer doesn't exist
     * @throws \Magento\Exception\StateException
     *      StateException::INPUT_MISMATCH if key doesn't match expected.
     *      StateException::INVALID_STATE_CHANGE if account already active.
     */
    public function validateAccountConfirmationKey($customerId, $confirmationKey);

    /**
     * Login a customer account using username and password
     *
     * @param string $username username in plain-text
     * @param string $password password in plain-text
     * @return Dto\Customer
     * @throws \Magento\Exception\AuthenticationException If unable to authenticate
     */
    public function authenticate($username, $password);

    /**
     * Checks if a given password matches the customer password.
     *
     * This function can be used instead of authenticate to re-verify that a logged in
     * user knows the password for sensitive actions.
     *
     * @param int $customerId
     * @param string $password
     * @return true
     * @throws \Magento\Exception\AuthenticationException
     */
    public function validatePassword($customerId, $password);

    /**
     * Change customer password.
     *
     * @param int $customerId
     * @param string $newPassword
     * @return void
     * @throws \Magento\Exception\NoSuchEntityException If customer with customerId is not found.
     */
    public function changePassword($customerId, $newPassword);

    /**
     * Check if password reset token is valid
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return void
     * @throws \Magento\Exception\StateException If token is expired or mismatched
     * @throws \Magento\Exception\InputException If token or customer id is invalid
     * @throws \Magento\Exception\NoSuchEntityException If customer doesn't exist
     */
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken);

    /**
     * Send an email to the customer with a password reset link.
     *
     * @param string $email
     * @param int $websiteId
     * @param string $template Type of email to send.  Must be one of the email constants.
     * @return void
     * @throws \Magento\Exception\NoSuchEntityException
     */
    public function sendPasswordResetLink($email, $websiteId, $template);


    /**
     * Reset customer password.
     *
     * @param int $customerId
     * @param string $password
     * @param string $resetToken
     * @return void
     * @throws \Magento\Exception\StateException If token is expired or mismatched
     * @throws \Magento\Exception\InputException If token or customer id is invalid
     * @throws \Magento\Exception\NoSuchEntityException If customer doesn't exist
     * @deprecated Use changePassword and validateResetPasswordLinkToken instead
     */
    public function resetPassword($customerId, $password, $resetToken);

    /**
     * Gets the account confirmation status
     *
     * @param int $customerId
     * @return string returns one of the account confirmation statuses
     */
    public function getConfirmationStatus($customerId);

    /**
     * Send Confirmation email.
     *
     * @param string $email email address of customer
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the customer to a product they were looking at after pressing confirmation link.
     * @return void
     * @throws \Magento\Exception\NoSuchEntityException If no customer found for provided email
     * @throws \Magento\Exception\StateException If confirmation is not needed
     */
    public function sendConfirmation($email, $redirectUrl = '');

    /**
     * Validate customer entity
     *
     * @param Dto\Customer $customer
     * @param Dto\Eav\AttributeMetadata[] $attributes
     * @return array|bool
     */
    public function validateCustomerData(Dto\Customer $customer, array $attributes);
}
