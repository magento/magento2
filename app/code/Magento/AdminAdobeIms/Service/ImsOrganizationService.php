<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\Framework\HTTP\Client\CurlFactory;

class ImsOrganizationService
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @param ImsConfig $adminImsConfig
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        CurlFactory $curlFactory
    ) {
        $this->adminImsConfig = $adminImsConfig;
        $this->curlFactory = $curlFactory;
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
        $configuredOrganizationId = $this->adminImsConfig->getOrganizationId();

        if ($configuredOrganizationId === '' || !$access_token) {
            throw new AdobeImsOrganizationAuthorizationException(
                __('Can\'t check user membership in organization.')
            );
        }

        try {
            $curl = $this->curlFactory->create();

            $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $curl->addHeader('cache-control', 'no-cache');
            $curl->addHeader('Authorization', 'Bearer ' . $access_token);

            $orgCheckUrl = $this->adminImsConfig->getOrganizationMembershipUrl($configuredOrganizationId);
            $curl->get($orgCheckUrl);

            if ($curl->getBody() === '') {
                throw new AdobeImsOrganizationAuthorizationException(
                    __('Could not check Organization Membership. Response is empty.')
                );
            }

            $response = $curl->getBody();

            if ($response !== 'true') {
                throw new AdobeImsOrganizationAuthorizationException(
                    __('User is not a member of configured Adobe Organization.')
                );
            }
        } catch (\Exception $exception) {
            throw new AdobeImsOrganizationAuthorizationException(
                __('Organization Membership check can\'t be performed')
            );
        }
    }
}
