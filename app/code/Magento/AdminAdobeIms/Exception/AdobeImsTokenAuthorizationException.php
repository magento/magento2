<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Exception;

use Magento\Framework\Exception\AuthorizationException;

/**
 * @api
 */
class AdobeImsTokenAuthorizationException extends AuthorizationException
{
    /**
     * @deprecated
     */
    const NOT_AUTHORIZED = "The consumer isn't authorized to access %resources.";
}
