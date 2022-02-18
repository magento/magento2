<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdminAdobeIms\Model\ImsConnection;

class ImsOrganizationAllocationService
{
    private ImsConnection $imsConnection;
    private ImsConfig $imsConfig;

    /**
     * @param ImsConnection $imsConnection
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        ImsConnection $imsConnection,
        ImsConfig $imsConfig
    ) {
        $this->imsConnection = $imsConnection;
        $this->imsConfig = $imsConfig;
    }

    /**
     * Check if admin is allocated to configured organization
     *
     * @param string $code
     * @return bool
     * @throws AdobeImsOrganizationAuthorizationException
     */
    public function checkOrganizationAllocation(string $code): bool
    {
        $configuredOrganization = $this->imsConfig->getOrganizationId();

        $customerOrganizations = $this->getCustomerOrganizationList($code);

        /**
         * walk through $customerOrganizations array and see if $configuredOrganization is there
         */

        return true;
    }

    /**
     * Get list of assigned organizations of admin
     *
     * @param string $code
     * @return array
     * @throws AdobeImsOrganizationAuthorizationException
     */
    private function getCustomerOrganizationList(string $code): array
    {
        $organizations = $this->imsConnection->getAssignedOrganizations($code);

        //90FC331D59DBA35E0A494204
        //AdobeOrg


        return [];
    }
}
