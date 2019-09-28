<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

/**
 * Interface for managing customers password.
 * @api
 * @since 100.0.6
 */
interface PasswordManagementInterface
{
    /**
     * Check if password reset token is valid.
     *
     * @param string $resetPasswordLinkToken
     *
     * @return bool True if the token is valid
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token is invalid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateResetPasswordLinkByToken($resetPasswordLinkToken);
}
