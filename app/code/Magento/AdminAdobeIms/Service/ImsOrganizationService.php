<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\Framework\Exception\InvalidArgumentException;

class ImsOrganizationService
{
    /**
     * Regex to verify a valid AdobeOrg Organization ID string.
     */
    private const ORGANIZATION_REGEX = '/([A-Z0-9]{24})(@AdobeOrg)?/i';

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

    /**
     * Check if OrganizationID matches pattern
     *
     * @param string $organizationId
     * @return string
     * @throws AdobeImsOrganizationAuthorizationException
     */
    private function validateAndExtractOrganizationId(string $organizationId): string
    {
        if (preg_match(self::ORGANIZATION_REGEX, $organizationId, $matches)) {
            if (!empty($matches) && isset($matches[1])) {
                return $matches[1];
            }
        }

        throw new AdobeImsOrganizationAuthorizationException(
            __('No valid organization ID provided')
        );
    }
}
