<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

/**
 * Declare functionality for getting the Adobe services user profile image
 * @api
 */
interface GetImageInterface
{
    /**
     * Retrieve user image from Adobe IMS
     *
     * @param string $accessToken
     * @param int $size
     * @return string
     */
    public function execute(string $accessToken, int $size = 276): string;
}
