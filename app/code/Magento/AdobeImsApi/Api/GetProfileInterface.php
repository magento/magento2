<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\AdobeImsApi\Api;

use Magento\Framework\Exception\AuthorizationException;

/**
 * Declare functionality to get profile
 *
 * @api
 */
interface GetProfileInterface
{
    /**
     * Get profile url
     *
     * @param string $code
     * @return array|bool|float|int|mixed|string|null
     * @throws AuthorizationException
     */
    public function getProfile(string $code);
}
