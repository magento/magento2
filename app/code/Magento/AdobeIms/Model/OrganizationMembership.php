<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\OrganizationMembershipInterface;
use Magento\Framework\HTTP\Client\CurlFactory;

/**
 * Check if user is a member of Adobe Organization
 */
class OrganizationMembership implements OrganizationMembershipInterface
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $imsConfig;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @param ConfigInterface $imsConfig
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        ConfigInterface $imsConfig,
        CurlFactory $curlFactory
    ) {
        $this->imsConfig = $imsConfig;
        $this->curlFactory = $curlFactory;
    }

    /**
     * @inheritDoc
     */
    public function checkOrganizationMembership(string $access_token): void
    {
        $configuredOrganizationId = $this->imsConfig->getOrganizationId();

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

            $orgCheckUrl = $this->imsConfig->getOrganizationMembershipUrl($configuredOrganizationId);
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
