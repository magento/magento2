<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * Class InvalidEmailOrPasswordException
 */
class InvalidEmailOrPasswordException extends AuthenticationException
{
    /**
     * @deprecated
     */
    const INVALID_EMAIL_OR_PASSWORD = 'Invalid email or password';
}
