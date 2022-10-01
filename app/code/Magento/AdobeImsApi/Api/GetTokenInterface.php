<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Framework\Exception\AuthorizationException;

/**
 * Declare functionality for getting user token
 * @api
 */
interface GetTokenInterface
{
    /**
     * Retrieve token and user information from Adobe IMS
     *
     * @param string $code
     * @return TokenResponseInterface
     * @throws AuthorizationException
     */
    public function execute(string $code): TokenResponseInterface;

    /**
     * Get token response
     *
     * @param string $code
     * @return TokenResponseInterface
     * @throws AuthorizationException
     */
    public function getTokenResponse(string $code): TokenResponseInterface;
}
