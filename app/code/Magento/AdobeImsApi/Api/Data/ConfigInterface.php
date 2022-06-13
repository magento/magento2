<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeImsApi\Api\Data;

/**
 * Class Config
 * @api
 */
interface ConfigInterface
{
    /**
     * Retrieve integration API key (Client ID)
     *
     * @return string|null
     */
    public function getApiKey():? string;

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
}
