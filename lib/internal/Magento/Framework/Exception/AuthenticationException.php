<?php
/**
 * Authentication exception
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * @api
 * @since 100.0.2
 */
class AuthenticationException extends LocalizedException
{
    /**
     * @deprecated
     */
    const AUTHENTICATION_ERROR = 'An authentication error occurred. Verify and try again.';
}
