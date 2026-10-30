<?php
/**
 * Authentication exception
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
