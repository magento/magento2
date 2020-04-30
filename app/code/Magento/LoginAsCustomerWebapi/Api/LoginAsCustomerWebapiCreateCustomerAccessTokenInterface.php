<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerWebapi\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface providing customer token generation for admin.
 *
 * @api
 */
interface LoginAsCustomerWebapiCreateCustomerAccessTokenInterface
{
    /**
     * Create access token for admin by customer id.
     *
     * Returns created token.
     *
     * @param int $customerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(int $customerId): string;
}
