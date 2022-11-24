<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Declare the the Adobe IMS integration config which is responsible for retrieving config
 * settings for Adobe Ims
 * @api
 */
interface ConfigInterface
{
    /**
     * Retrieve integration API key (Client ID)
     *
     * @return string|null
     */
    public function getApiKey(): ?string;

    /**
     * Retrieve integration API private KEY (Client secret)
     *
     * @return string
     */
    public function getPrivateKey(): string;

    /**
     * Retrieve token URL
     *
     * @return string
     */
    public function getTokenUrl(): string;

    /**
     * Retrieve auth URL
     *
     * @return string
     */
    public function getAuthUrl(): string;

    /**
     * Retrieve Callback URL
     *
     * @return string
     */
    public function getCallBackUrl(): string;

    /**
     * Return logout url for AdobeSdk.
     *
     * @param string $accessToken
     * @param string $redirectUrl
     * @return string
     */
    public function getLogoutUrl(string $accessToken, string $redirectUrl = ''): string;

    /**
     * Return image url for AdobeSdk.
     *
     * @return string
     */
    public function getProfileImageUrl(): string;

    /**
     * Get Profile URL
     *
     * @return string
     */
    public function getProfileUrl(): string;

    /**
     * Get Token validation url
     *
     * @param string $code
     * @param string $tokenType
     * @return string
     */
    public function getValidateTokenUrl(string $code, string $tokenType): string;

    /**
     * Generate the AdminAdobeIms AuthUrl with given clientID or the ClientID stored in the config
     *
     * @param string|null $clientId
     * @return string
     */
    public function getAdminAdobeImsAuthUrl(?string $clientId): string;

    /**
     * Generate the AdminAdobeIms AuthUrl for reAuth
     *
     * @return string
     */
    public function getAdminAdobeImsReAuthUrl(): string;

    /**
     * Get BackendLogout URL
     *
     * @param string $accessToken
     * @return string
     */
    public function getBackendLogoutUrl(string $accessToken): string;

    /**
     * IMS certificate (public key) location retrieval
     *
     * @param string $fileName
     * @return string
     */
    public function getCertificateUrl(string $fileName): string;

    /**
     * Get url to check organization membership
     *
     * @param string $orgId
     * @return string
     */
    public function getOrganizationMembershipUrl(string $orgId): string;

    /**
     * Enable Admin Adobe IMS Module and set Client ID and Client Secret and Organization ID and Two Factor Enabled
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $organizationId
     * @param bool $isAdobeIms2FAEnabled
     * @return void
     * @throws LocalizedException
     */
    public function enableModule(
        string $clientId,
        string $clientSecret,
        string $organizationId,
        bool $isAdobeIms2FAEnabled
    ): void;

    /**
     * Disable Admin Adobe IMS Module and unset Client ID and Client Secret from config
     *
     * @return void
     */
    public function disableModule(): void;

    /**
     * Retrieve Organization Id
     *
     * @return string
     */
    public function getOrganizationId(): string;
}
