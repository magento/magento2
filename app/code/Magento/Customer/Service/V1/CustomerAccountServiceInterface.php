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
 * Interface CustomerAccountServiceInterface
 */
interface CustomerAccountServiceInterface
{
    // Constants for the type of new account email to be sent
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    // welcome email, when confirmation is enabled
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    // email with confirmation link

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

    const MIN_PASSWORD_LENGTH = 6;

    /**
     * Create Customer Account
     *
     * @param \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails
     * @param string $password If null then a random password will be assigned. Disregard if $hash is not empty.
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the customer to a product they were looking at after pressing confirmation link.
     * @return \Magento\Customer\Service\V1\Data\Customer
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     */
    public function createCustomer(
        \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails,
        $password = null,
        $redirectUrl = ''
    );

    /**
     * Create Customer Account with provided hashed password. Should not be exposed as a webapi.
     *
     * @param \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails
     * @param string $hash Password hash that we can save directly
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the customer to a product they were looking at after pressing confirmation link.
     * @return \Magento\Customer\Service\V1\Data\Customer
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     */
    public function createCustomerWithPasswordHash(
        \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails,
        $hash,
        $redirectUrl = ''
    );

    /**
     * Update Customer Account and its details.
     * CustomerDetails contains an array of Address Data. In the event that no change was made to addresses
     * the array must be null.
     *
     * @param string $customerId
     * @param \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerDetails is not found.
     * @return bool True if this customer was updated
     */
    public function updateCustomer($customerId, \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails);

    /**
     * Retrieve Customer
     *
     * @param string $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    public function getCustomer($customerId);

    /**
     * Used to activate a customer account using a key that was sent in a confirmation e-mail.
     *
     * @param string $customerId
     * @param string $confirmationKey Sent to customer in an confirmation e-mail.
     * @return \Magento\Customer\Service\V1\Data\Customer
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer doesn't exist
     * @throws \Magento\Framework\Exception\State\InputMismatchException if the token is invalid
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException if account already active
     */
    public function activateCustomer($customerId, $confirmationKey);

    /**
     * Retrieve customers which match a specified criteria
     *
     * @param \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria
     * @throws \Magento\Framework\Exception\InputException if there is a problem with the input
     * @return \Magento\Customer\Service\V1\Data\SearchResults containing Data\CustomerDetails
     */
    public function searchCustomers(\Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria);

    /**
     * Login a customer account using username and password
     *
     * @param string $username username in plain-text
     * @param string $password password in plain-text
     * @return \Magento\Customer\Service\V1\Data\Customer
     * @throws \Magento\Framework\Exception\AuthenticationException If unable to authenticate
     * @throws \Magento\Framework\Exception\EmailNotConfirmedException If this is an unconfirmed account
     * @throws \Magento\Framework\Exception\InvalidEmailOrPasswordException If email or password is invalid
     */
    public function authenticate($username, $password);

    /**
     * Change customer password.
     *
     * @param string $customerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool True if password changed
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     * @throws \Magento\Framework\Exception\InvalidEmailOrPasswordException If invalid currentPassword is supplied
     */
    public function changePassword($customerId, $currentPassword, $newPassword);

    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     * @todo this method has to be removed when the checkout process refactored in the way it won't require to pass
     *       a password through requests
     */
    public function getPasswordHash($password);

    /**
     * Check if password reset token is valid
     *
     * @param string $customerId
     * @param string $resetPasswordLinkToken
     * @return bool True if the token is valid
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or customer id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer doesn't exist
     */
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken);

    /**
     * Send an email to the customer with a password reset link.
     *
     * @param string $email
     * @param string $template Type of email to send.  Must be one of the email constants.
     * @param string $websiteId Optional id.  If the website id is not provided
     *                       it will be retrieved from the store manager
     * @return void
     */
    public function initiatePasswordReset($email, $template, $websiteId = null);

    /**
     * Reset customer password.
     *
     * @param string $customerId
     * @param string $resetToken Token sent to customer via e-mail
     * @param string $newPassword
     * @return void
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or customer id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer doesn't exist
     */
    public function resetPassword($customerId, $resetToken, $newPassword);

    /**
     * Gets the account confirmation status
     *
     * @param string $customerId
     * @return string returns one of the account confirmation statuses
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     */
    public function getConfirmationStatus($customerId);

    /**
     * Resend confirmation email.
     *
     * @param string $email email address of customer
     * @param string $websiteId website
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the customer to a product they were looking at after pressing confirmation link.
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no customer found for provided email
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException If confirmation is not needed
     */
    public function resendConfirmation($email, $websiteId = null, $redirectUrl = '');

    /**
     * Validate customer entity
     *
     * @param \Magento\Customer\Service\V1\Data\Customer $customer
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[] $attributes
     * @return \Magento\Customer\Service\V1\Data\CustomerValidationResults
     */
    public function validateCustomerData(
        \Magento\Customer\Service\V1\Data\Customer $customer,
        array $attributes = array()
    );

    /**
     * Indicates if the Customer for the provided customerId is restricted to being read only
     * for the currently logged in user, or if modifications can be made.
     *
     * @param string $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     * @return bool true if modifications can be made; false if read only.
     */
    public function canModify($customerId);

    /**
     * Indicates if the Customer for the currently logged in user as specified by the provided
     * customerId can be deleted.
     *
     * @param string $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     * @return bool true if the customer can be deleted
     */
    public function canDelete($customerId);

    /**
     * Retrieve customer details
     *
     * @param string $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     * @return \Magento\Customer\Service\V1\Data\CustomerDetails
     */
    public function getCustomerDetails($customerId);

    /**
     * Delete Customer
     *
     * @param string $customerId
     * @throws \Magento\Customer\Exception If something goes wrong during delete
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     * @return bool True if the customer was deleted
     */
    public function deleteCustomer($customerId);

    /**
     * Check if the email has not been associated with a customer account in given website
     *
     * @param string $customerEmail
     * @param int $websiteId If not set, will use the current websiteId
     * @return bool true if the email is not associated with a customer account in given website
     */
    public function isEmailAvailable($customerEmail, $websiteId = null);

    /**
     * Check store availability for customer given the customerId
     *
     * @param string $customerWebsiteId
     * @param string $storeId
     * @return bool
     */
    public function isCustomerInStore($customerWebsiteId, $storeId);

    /**
     * Retrieve customer
     *
     * @param string $customerEmail
     * @param string $websiteId If not set, will use the current websiteId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerEmail is not found.
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    public function getCustomerByEmail($customerEmail, $websiteId = null);

    /**
     * Retrieve customer details
     *
     * @param string $customerEmail
     * @param string $websiteId If not set, will use the current websiteId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerEmail is not found.
     * @return \Magento\Customer\Service\V1\Data\CustomerDetails
     */
    public function getCustomerDetailsByEmail($customerEmail, $websiteId = null);

    /**
     * Update Customer Account and its details.
     * CustomerDetails contains an array of Address Data. In the event that no change was made to addresses
     * the array must be null.
     *
     * @param string $customerEmail
     * @param \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails
     * @param string $websiteId If not set, will use the current websiteId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerDetails is not found.
     * @return bool True if this customer was updated
     */
    public function updateCustomerByEmail(
        $customerEmail,
        \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails,
        $websiteId = null
    );

    /**
     * Delete Customer by email
     *
     * @param string $customerEmail
     * @param string $websiteId If not set, will use the current websiteId
     * @throws \Magento\Customer\Exception If something goes wrong during delete
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     * @return bool True if the customer was deleted
     */
    public function deleteCustomerByEmail($customerEmail, $websiteId = null);
}
