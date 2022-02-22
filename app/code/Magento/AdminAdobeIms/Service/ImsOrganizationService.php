<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Psr\Log\InvalidArgumentException;

class ImsOrganizationService
{
    public const ORGANIZATION_ERROR_MESSAGE = 'The Adobe ID you\'re using does not belong to the organization ' .
        'that controlling this Commerce instance. Contact your administrator so he can add your Adobe ID' .
        'to the organization.';

    /**
     * Regex to verify a valid AdobeOrg Organization ID string.
     */
    private const ORGANIZATION_REGEX = '/([A-Z0-9]{24})(@AdobeOrg)?/i';

    private ImsConfig $imsConfig;

    /**
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        ImsConfig $imsConfig
    ) {
        $this->imsConfig = $imsConfig;
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
            throw new InvalidArgumentException(
                __('No roles assigned for profile')
            );
        }

        $customerOrganizations = $this->getCustomerOrganizationList($profile['roles']);
        $configuredOrganization = $this->imsConfig->getOrganizationId();

        if (!in_array($configuredOrganization, $customerOrganizations, true)) {
            throw new AdobeImsOrganizationAuthorizationException(
                __(self::ORGANIZATION_ERROR_MESSAGE)
            );
        }

        return true;
    }

    /**
     * Get list of assigned organizations of admin
     *
     * @param array $profileRoles
     * @return array
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
     * @param string $organizationId
     * @return string
     * @throws InvalidArgumentException
     */
    public function validateAndExtractOrganizationId(string $organizationId): string
    {
        if (preg_match(self::ORGANIZATION_REGEX, $organizationId, $matches)) {
            if (!empty($matches) && isset($matches[1])) {
                return $matches[1];
            }
        }

        throw new InvalidArgumentException(
            __('No valid organization ID provided')
        );
    }
}
