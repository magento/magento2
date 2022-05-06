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
     * Check if profile is allocated to configured organization
     *
     * @param array $profile
     * @return bool
     * @throws AdobeImsOrganizationAuthorizationException
     */
    public function checkOrganizationAllocation(array $profile): bool
    {
        if (empty($profile['roles'])) {
            throw new AdobeImsOrganizationAuthorizationException(
                __('No roles assigned for profile.')
            );
        }

        $customerOrganizations = $this->getCustomerOrganizationList($profile['roles']);
        $configuredOrganization = $this->adminImsConfig->getOrganizationId();

        if (!in_array($configuredOrganization, $customerOrganizations, true)) {
            throw new AdobeImsOrganizationAuthorizationException(
                __('Profile is not assigned to defined organization.')
            );
        }

        return true;
    }

    /**
     * Get list of assigned organizations of admin
     *
     * @param array $profileRoles
     * @return array
     * @throws AdobeImsOrganizationAuthorizationException
     */
    private function getCustomerOrganizationList(array $profileRoles): array
    {
        $organizationList = [];
        foreach ($profileRoles as $role) {
            $organizationId = $this->validateAndExtractOrganizationId($role['organization']);
            $organizationList[$role['named_role']] = $organizationId;
        }

        return $organizationList;
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
