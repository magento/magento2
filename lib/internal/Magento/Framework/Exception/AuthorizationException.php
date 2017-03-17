<?php
/**
 * Authorization service exception
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class AuthorizationException extends LocalizedException
{
    /**
     * @deprecated
     */
    const NOT_AUTHORIZED = 'Consumer is not authorized to access %resources';
}
