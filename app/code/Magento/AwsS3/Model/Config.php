<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configuration for AWS S3.
 */
class Config
{
    public const PATH_DRIVER = 'system/file_system/driver';
    public const PATH_REGION = 'system/file_system/region';
    public const PATH_BUCKET = 'system/file_system/bucket';
    public const PATH_ACCESS_KEY = 'system/file_system/access_key';
    public const PATH_SECRET_KEY = 'system/file_system/secret_key';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
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
        return (string)$this->config->getValue(self::PATH_REGION);
    }

    /**
     * Retrieves bucket.
     *
     * @return string
     */
    public function getBucket(): string
    {
        return (string)$this->config->getValue(self::PATH_BUCKET);
    }

    /**
     * Retrieves access key.
     *
     * @return string
     */
    public function getAccessKey(): string
    {
        return (string)$this->config->getValue(self::PATH_ACCESS_KEY);
    }

    /**
     * Retrieves secret key.
     *
     * @return string
     */
    public function getSecretKey(): string
    {
        return (string)$this->config->getValue(self::PATH_SECRET_KEY);
    }
}
