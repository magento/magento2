<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Analytics\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Validator\BearerTokenValidator;

/**
 * Overrides authorization config to always allow analytics token to be used as bearer
 */
class BearerTokenValidatorPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /***
     * Always allow access token for analytics to be used as bearer
     *
     * @param BearerTokenValidator $subject
     * @param bool $result
     * @param Integration $integration
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsIntegrationAllowedAsBearerToken(
        BearerTokenValidator $subject,
        bool $result,
        Integration $integration
    ): bool {
        return $result || $integration->getName() === $this->config->getValue('analytics/integration_name');
    }
}
