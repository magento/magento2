<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\GraphQl;

use Magento\Framework\App\DeploymentConfig;

/**
 * Reads GraphQL request limits from deployment configuration (app/etc/env.php).
 */
class RequestConfiguration
{
    private const CONFIG_PATH = 'graphql/max_request_body_size';

    private const DEFAULT_MAX_REQUEST_BODY_SIZE = 1048576;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        private readonly DeploymentConfig $deploymentConfig
    ) {
    }

    /**
     * Maximum allowed GraphQL POST body size in bytes (0 = no limit).
     *
     * When the key is missing or empty, uses default (1 MiB). Any non‑negative integer is allowed,
     * including values smaller than 1 MiB (e.g. 524288 for 512 KiB).
     *
     * @return int
     */
    public function getMaxRequestBodySize(): int
    {
        $value = $this->deploymentConfig->get(self::CONFIG_PATH);

        if ($value === null || $value === '') {
            return self::DEFAULT_MAX_REQUEST_BODY_SIZE;
        }

        return max(0, (int) $value);
    }
}
