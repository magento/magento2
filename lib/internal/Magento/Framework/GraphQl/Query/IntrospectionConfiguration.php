<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\App\DeploymentConfig;

/**
 * Class for fetching the availability of introspection queries
 */
class IntrospectionConfiguration
{
    private const CONFIG_PATH_DISABLE_INTROSPECTION = 'graphql/disable_introspection';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        DeploymentConfig $deploymentConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Check the the environment config to determine if introspection should be disabled.
     *
     * @return bool
     */
    public function isIntrospectionDisabled(): bool
    {
        return (bool)$this->deploymentConfig->get(self::CONFIG_PATH_DISABLE_INTROSPECTION);
    }
}
