<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;

interface EmailNotificationInterface
{
    /**
     * Constants for the type of new account email to be sent
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Send notification to customer when email and/or password changed
     *
     * @param CustomerInterface $savedCustomer
     * @param string $origCustomerEmail
     * @param bool $isPasswordChanged
     * @return void
     */
    public function credentialsChanged(
        CustomerInterface $savedCustomer,
        $origCustomerEmail,
        $isPasswordChanged = false
    );

    /**
     * Send email with new customer password
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function passwordReminder(CustomerInterface $customer);

    /**
     * Send email with reset password confirmation link
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function passwordResetConfirmation(CustomerInterface $customer);

    /**
     * Send email with new account related information
     *
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     */
    public function newAccount(
        CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    );
}
