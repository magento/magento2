<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

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
     * @param string $redirectUrl
     * @return string
     */
    public function getLogoutUrl(string $redirectUrl = ''): string;

    /**
     * Return image url for AdobeSdk.
     *
     * @return string
     */
    public function getProfileImageUrl(): string;
}
