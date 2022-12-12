<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Interface for managing customers accounts.
 * @api
 * @since 100.0.2
 */
interface AccountManagementInterface
{
    /**#@+
     * Constant for confirmation status
     */
    public const ACCOUNT_CONFIRMED = 'account_confirmed';
    public const ACCOUNT_CONFIRMATION_REQUIRED = 'account_confirmation_required';
    public const ACCOUNT_CONFIRMATION_NOT_REQUIRED = 'account_confirmation_not_required';
    public const MAX_PASSWORD_LENGTH = 256;
    /**#@-*/

    /**
     * Create customer account. Perform necessary business operations like sending email.
     *
     * @param CustomerInterface $customer
     * @param string|null $password
     * @param string $redirectUrl
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function createAccount(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    );

    /**
     * Create customer account using provided hashed password. Should not be exposed as a webapi.
     *
     * @param CustomerInterface $customer
     * @param string $hash Password hash that we can save directly
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the customer to a product they were looking at after pressing confirmation link.
     * @return CustomerInterface
     * @throws InputException If bad input is provided
     * @throws InputMismatchException If the provided email is already used
     * @throws LocalizedException
     */
    public function createAccountWithPasswordHash(
        CustomerInterface $customer,
        string            $hash,
        string $redirectUrl = ''
    ): CustomerInterface;

    /**
     * Validate customer data.
     *
     * @param CustomerInterface $customer
     * @return ValidationResultsInterface
     * @throws LocalizedException
     */
    public function validate(CustomerInterface $customer): ValidationResultsInterface;

    /**
     * Check if customer can be deleted.
     *
     * @param int $customerId
     * @return bool
     * @throws NoSuchEntityException If group is not found
     * @throws LocalizedException
     */
    public function isReadonly(int $customerId): bool;

    /**
     * Activate a customer account using a key that was sent in a confirmation email.
     *
     * @param string $email
     * @param string $confirmationKey
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function activate(string $email, string $confirmationKey): CustomerInterface;

    /**
     * Activate a customer account using a key that was sent in a confirmation email.
     *
     * @param int $customerId
     * @param string $confirmationKey
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function activateById(int $customerId, string $confirmationKey): CustomerInterface;

    /**
     * Authenticate a customer by username and password
     *
     * @param string $email
     * @param string $password
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function authenticate(string $email, string $password): CustomerInterface;

    /**
     * Change customer password.
     *
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws LocalizedException
     */
    public function changePassword(string $email, string $currentPassword, string $newPassword): bool;

    /**
     * Change customer password.
     *
     * @param int $customerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws LocalizedException
     */
    public function changePasswordById(int $customerId, string $currentPassword, string $newPassword): bool;

    /**
     * Send an email to the customer with a password reset link.
     *
     * @param string $email
     * @param string $template
     * @param int|null $websiteId
     * @return bool true on success
     * @throws LocalizedException
     */
    public function initiatePasswordReset(string $email, string $template, int $websiteId = null): bool;

    /**
     * Reset customer password.
     *
     * @param string $email If empty value given then the customer
     * will be matched by the RP token.
     * @param string $resetToken
     * @param string $newPassword
     *
     * @return bool true on success
     * @throws LocalizedException
     * @throws InputException
     */
    public function resetPassword(string $email, string $resetToken, string $newPassword): bool;

    /**
     * Check if password reset token is valid.
     *
     * @param int $customerId If null is given then a customer
     * will be matched by the RP token.
     * @param string $resetPasswordLinkToken
     *
     * @return bool True if the token is valid
     * @throws InputMismatchException If token is mismatched
     * @throws ExpiredException If token is expired
     * @throws InputException If token or customer id is invalid
     * @throws NoSuchEntityException If customer doesn't exist
     * @throws LocalizedException
     */
    public function validateResetPasswordLinkToken(int $customerId, string $resetPasswordLinkToken): bool;

    /**
     * Gets the account confirmation status.
     *
     * @param int $customerId
     * @return string
     * @throws LocalizedException
     */
    public function getConfirmationStatus(int $customerId): string;

    /**
     * Resend confirmation email.
     *
     * @param string $email
     * @param int $websiteId
     * @param string $redirectUrl
     * @return bool true on success
     * @throws LocalizedException
     */
    public function resendConfirmation(string $email, int $websiteId, string $redirectUrl = ''): bool;

    /**
     * Check if given email is associated with a customer account in given website.
     *
     * @param string $customerEmail
     * @param int|null $websiteId If not set, will use the current websiteId
     * @return bool
     * @throws LocalizedException
     */
    public function isEmailAvailable(string $customerEmail, int $websiteId = null): bool;

    /**
     * Check store availability for customer given the customerId.
     *
     * @param int $customerWebsiteId
     * @param int $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function isCustomerInStore(int $customerWebsiteId, int $storeId): bool;

    /**
     * Retrieve default billing address for the given customerId.
     *
     * @param int $customerId
     * @return AddressInterface
     * @throws NoSuchEntityException If the customer Id is invalid
     * @throws LocalizedException
     */
    public function getDefaultBillingAddress(int $customerId): Data\AddressInterface;

    /**
     * Retrieve default shipping address for the given customerId.
     *
     * @param int $customerId
     * @return AddressInterface
     * @throws NoSuchEntityException If the customer Id is invalid
     * @throws LocalizedException
     */
    public function getDefaultShippingAddress(int $customerId): AddressInterface;

    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash(string $password): string;
}
