<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

use Magento\Framework\Exception\AuthorizationException;

/**
 * Check if user is a member of Adobe Organization
 */
interface OrganizationMembershipInterface
{
    /**
     * Check if user is a member of Adobe Organization
     *
     * @param string $access_token
     * @return void
     * @throws AuthorizationException
     */
    public function checkOrganizationMembership(string $access_token): void;
}
