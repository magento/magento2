<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Declare functionality for user login from the Adobe account
 *
 * @api
 */
interface LogInInterface
{
    /**
     * Log in User to Adobe Account
     *
     * @param int $userId
     * @param TokenResponseInterface $tokenResponse
     * @throws CouldNotSaveException
     */
    public function execute(int $userId, TokenResponseInterface $tokenResponse): void;
}
