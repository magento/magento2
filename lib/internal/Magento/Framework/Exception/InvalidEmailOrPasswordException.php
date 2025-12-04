<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception;

/**
 * @api
 * @since 100.0.2
 */
class InvalidEmailOrPasswordException extends AuthenticationException
{
    /**
     * @deprecated
     */
    const INVALID_EMAIL_OR_PASSWORD = 'Invalid email or password';
}
