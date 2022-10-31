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
 *
 * @deprecated
 * @see \Magento\AdobeIms\Exception\AdobeImsOrganizationAuthorizationException
 */
class AdobeImsOrganizationAuthorizationException extends AuthorizationException
{
    public const ERROR_MESSAGE = 'The Adobe ID you\'re using does not belong to the organization ' .
        'that controls this Commerce instance. Contact your administrator so he can add your Adobe ID ' .
        'to the organization.';
}
