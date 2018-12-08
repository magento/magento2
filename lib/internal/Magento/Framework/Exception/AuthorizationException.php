<?php
/**
 * Authorization service exception
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception;

/**
 * @api
 */
class AuthorizationException extends LocalizedException
{
    /**
     * @deprecated
     */
    const NOT_AUTHORIZED = "The consumer isn't authorized to access %resources.";
}
