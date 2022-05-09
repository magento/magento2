<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;

class ImsOrganizationService
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(
        ImsConfig $adminImsConfig
    ) {
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Check if user is assigned to organization
     *
     * @param string $token
     * @return bool
     * @throws AdobeImsOrganizationAuthorizationException
     */
    public function checkOrganizationAllocation(string $token): bool
    {
        $configuredOrganization = $this->adminImsConfig->getOrganizationId();

        //@TODO CABPI-324: Change Org check to use new endpoint
        if (!$configuredOrganization || !$token) {
            throw new AdobeImsOrganizationAuthorizationException(
                __('User is not assigned to defined organization.')
            );
        }

        return true;
    }
}
