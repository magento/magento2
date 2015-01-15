<?php
/**
 * Authorization service exception
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class AuthorizationException extends LocalizedException
{
    const NOT_AUTHORIZED = 'Consumer is not authorized to access %resources';
}
