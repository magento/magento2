<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Magento\AwsS3\Model\Config;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\RemoteStorage\Driver\DriverFactoryInterface;

/**
 * Creates a pre-configured instance of AWS S3 driver.
 */
class AwsS3Factory implements DriverFactoryInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Creates an instance of AWS S3 driver.
     *
     * @return DriverInterface
     */
    public function create(): DriverInterface
    {
        return new AwsS3(
            $this->config->getRegion(),
            $this->config->getBucket(),
            $this->config->getAccessKey(),
            $this->config->getSecretKey()
        );
    }
}
