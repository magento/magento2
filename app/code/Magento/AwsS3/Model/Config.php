<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Model;

use Magento\Framework\App\DeploymentConfig;

/**
 * Configuration for AWS S3.
 */
class Config
{
    public const PATH_REGION = 'remote_storage/region';
    public const PATH_BUCKET = 'remote_storage/bucket';
    public const PATH_ACCESS_KEY = 'remote_storage/access_key';
    public const PATH_SECRET_KEY = 'remote_storage/secret_key';
    public const PATH_PREFIX = 'remote_storage/prefix';

    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieves region.
     *
     * @return string
     */
    public function getRegion(): string
    {
        return (string)$this->config->get(self::PATH_REGION);
    }

    /**
     * Retrieves bucket.
     *
     * @return string
     */
    public function getBucket(): string
    {
        return (string)$this->config->get(self::PATH_BUCKET);
    }

    /**
     * Retrieves access key.
     *
     * @return string
     */
    public function getAccessKey(): string
    {
        return (string)$this->config->get(self::PATH_ACCESS_KEY);
    }

    /**
     * Retrieves secret key.
     *
     * @return string
     */
    public function getSecretKey(): string
    {
        return (string)$this->config->get(self::PATH_SECRET_KEY);
    }

    /**
     * Retrieves prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return (string)$this->config->get(self::PATH_PREFIX, '');
    }
}
