<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\Validator;

use Magento\Integration\Model\Config\AuthorizationConfig;
use Magento\Integration\Model\Integration;

/**
 * Validate if an integration use the access token as a bearer token
 */
class BearerTokenValidator
{
    /**
     * @var AuthorizationConfig
     */
    private AuthorizationConfig $authorizationConfig;

    /**
     * @param AuthorizationConfig $authorizationConfig
     */
    public function __construct(AuthorizationConfig $authorizationConfig)
    {
        $this->authorizationConfig = $authorizationConfig;
    }

    /**
     * Validate an integration's access token can be used as a standalone bearer token
     *
     * @param Integration $integration
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isIntegrationAllowedAsBearerToken(Integration $integration): bool
    {
        return $this->authorizationConfig->isIntegrationAsBearerEnabled();
    }
}
