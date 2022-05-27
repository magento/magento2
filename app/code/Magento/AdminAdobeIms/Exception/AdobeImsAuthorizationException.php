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
class AdobeImsAuthorizationException extends AuthorizationException
{
    public const ERROR_MESSAGE = 'The Adobe ID you\'re using is not added to this Commerce instance. ' .
        'Contact your organization administrator to request access.';
}
