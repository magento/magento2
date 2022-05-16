<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdminAdobeIms\Model\ImsConnection;

class ImsOrganizationService
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var ImsConnection
     */
    private ImsConnection $adminImsConnection;

    /**
     * @param ImsConfig $adminImsConfig
     * @param ImsConnection $adminImsConnection
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        ImsConnection $adminImsConnection
    ) {
        $this->adminImsConfig = $adminImsConfig;
        $this->adminImsConnection = $adminImsConnection;
    }

    /**
     * Check if user is a member of Adobe Organization
     *
     * @param string $access_token
     * @return void
     * @throws AdobeImsOrganizationAuthorizationException
     */
    public function checkOrganizationMembership(string $access_token): void
    {
        $configuredOrganization = $this->adminImsConfig->getOrganizationId();

        if ($configuredOrganization === '' || !$access_token) {
            throw new AdobeImsOrganizationAuthorizationException(
                __('Can\'t check user membership in organization.')
            );
        }

        $this->adminImsConnection->organizationMembership($configuredOrganization, $access_token);
    }
}
