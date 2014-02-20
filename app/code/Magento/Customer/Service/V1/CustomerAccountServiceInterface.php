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
    /** account response status */
    const ACCOUNT_CONFIRMATION = "confirmation";
    const ACCOUNT_REGISTERED = "registered";

    // Constants for the type of new account email to be sent
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';     // welcome email, when confirmation is disabled
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';       // welcome email, when confirmation is enabled
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation'; // email with confirmation link

    /**
     * Create Customer Account
     *
     * @param Dto\Customer $customer
     * @param Dto\Address[] $addresses
     * @param string $password
     * @param string $confirmationBackUrl
     * @param string $registeredBackUrl
     * @param int $storeId
     * @return Dto\Response\CreateCustomerAccountResponse
     * @throws \Exception If something goes wrong during save
     * @throws \Magento\Exception\InputException If bad input is provided
     * @throws \Magento\Exception\StateException If the provided email is already used
     */
    public function createAccount(
        Dto\Customer $customer,
        array $addresses,
        $password = null,
        $confirmationBackUrl = '',
        $registeredBackUrl = '',
        $storeId = 0
    );

    /**
     * Used to activate a customer account using a key that was sent in a confirmation e-mail.
     *
     * @param int $customerId
     * @param string $key
     * @return Dto\Customer
     * @throws \Magento\Exception\NoSuchEntityException If customer doesn't exist
     * @throws \Magento\Exception\StateException
     *      StateException::INPUT_MISMATCH if key doesn't match expected.
     *      StateException::INVALID_STATE_CHANGE if account already active.
     */
    public function activateAccount($customerId, $key);

    /**
     * Login a customer account using username and password
     *
     * @param string $username username in plain-text
     * @param string $password password in plain-text
     * @return Dto\Customer
     * @throws \Magento\Exception\AuthenticationException if unable to authenticate
     */
    public function authenticate($username, $password);

    /**
     * Check if password reset token is valid
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return void
     * @throws \Magento\Exception\StateException if token is expired or mismatched
     * @throws \Magento\Exception\InputException if token or customer id is invalid
     * @throws \Magento\Exception\NoSuchEntityException if customer doesn't exist
     */
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken);

    /**
     * Send an email to the customer with a password reset link.
     *
     * @param string $email
     * @param int $websiteId
     * @return void
     * @throws \Magento\Exception\NoSuchEntityException
     */
    public function sendPasswordResetLink($email, $websiteId);


    /**
     * Reset customer password.
     *
     * @param int $customerId
     * @param string $password
     * @param string $resetToken
     * @return void
     * @throws \Magento\Exception\StateException if token is expired or mismatched
     * @throws \Magento\Exception\InputException if token or customer id is invalid
     * @throws \Magento\Exception\NoSuchEntityException if customer doesn't exist
     */
    public function resetPassword($customerId, $password, $resetToken);

    /*
     * Send Confirmation email
     *
     * @param string $email email address of customer
     * @return void
     * @throws \Magento\Exception\NoSuchEntityException if no customer found for provided email
     * @throws \Magento\Exception\StateException if confirmation is not needed
     */
    public function sendConfirmation($email);

    /**
     * Validate customer entity
     *
     * @param Dto\Customer $customer
     * @param Dto\Eav\AttributeMetadata[] $attributes
     * @return array|bool
     */
    public function validateCustomerData(Dto\Customer $customer, array $attributes);

}
