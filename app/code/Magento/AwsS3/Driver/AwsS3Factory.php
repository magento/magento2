<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Magento\AwsS3\Model\Config;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Driver\DriverFactoryInterface;

/**
 * Creates a pre-configured instance of AWS S3 driver.
 */
class AwsS3Factory implements DriverFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * Creates an instance of AWS S3 driver.
     *
     * @return DriverInterface
     */
    public function create(): DriverInterface
    {
        $config = [
            'region' => $this->config->getRegion(),
            'version' => 'latest'
        ];

        $key = $this->config->getAccessKey();
        $secret = $this->config->getSecretKey();

        if ($key && $secret) {
            $config['credentials'] = [
                'key' => $key,
                'secret' => $secret,
            ];
        }

        return $this->objectManager->create(
            AwsS3::class,
            [
                'adapter' => $this->objectManager->create(
                    AwsS3Adapter::class,
                    [
                        'client' => $this->objectManager->create(S3Client::class, ['args' => $config]),
                        'bucket' => $this->config->getBucket(),
                        'prefix' => $this->config->getPrefix()
                    ]
                )
            ]
        );
    }
}
