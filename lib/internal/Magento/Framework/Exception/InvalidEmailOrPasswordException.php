<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * @api
 * @since 2.0.0
 */
class InvalidEmailOrPasswordException extends AuthenticationException
{
    /**
     * @deprecated
     */
    const INVALID_EMAIL_OR_PASSWORD = 'Invalid email or password';
}
